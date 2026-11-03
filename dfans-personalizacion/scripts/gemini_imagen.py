"""
D'FANS - Generación de imágenes con la API de Gemini (19/10/2026).

La clave NO se escribe en ningún archivo: se lee de la variable de entorno GEMINI_API_KEY
(o, si el proceso aún no la tiene, del registro de Windows: HKCU\\Environment).
Nunca se imprime la clave; viaja solo en la cabecera x-goog-api-key.

Uso:
  python gemini_imagen.py modelos
      Lista los modelos de la cuenta que pueden generar imágenes.
  python gemini_imagen.py generar --prompt "..." --salida ruta.png [--ref foto.jpg ...] [--aspecto 3:4] [--modelo NOMBRE]
      Genera una imagen (con fotos de referencia opcionales).
  python gemini_imagen.py lote lote.json [--modelo NOMBRE]
      Genera varias: lista de {"prompt", "salida", "refs": [...], "aspecto"}.
"""
import argparse
import base64
import json
import mimetypes
import os
import sys
import time
import urllib.error
import urllib.request

API = 'https://generativelanguage.googleapis.com/v1beta'
MODELO_POR_DEFECTO = 'gemini-2.5-flash-image'


def clave():
    valor = os.environ.get('GEMINI_API_KEY')
    if not valor and sys.platform == 'win32':
        try:
            import winreg
            with winreg.OpenKey(winreg.HKEY_CURRENT_USER, 'Environment') as k:
                valor = winreg.QueryValueEx(k, 'GEMINI_API_KEY')[0]
        except OSError:
            valor = None
    if not valor:
        sys.exit('Falta la clave: guarda GEMINI_API_KEY en Windows (setx GEMINI_API_KEY "...").')
    return valor.strip()


def pedir(metodo, ruta, cuerpo=None):
    datos = json.dumps(cuerpo).encode('utf-8') if cuerpo is not None else None
    req = urllib.request.Request(API + ruta, data=datos, method=metodo, headers={
        'x-goog-api-key': clave(),
        'Content-Type': 'application/json',
    })
    try:
        with urllib.request.urlopen(req, timeout=180) as r:
            return json.loads(r.read().decode('utf-8'))
    except urllib.error.HTTPError as e:
        try:
            msg = json.loads(e.read().decode('utf-8')).get('error', {}).get('message', '')
        except Exception:
            msg = ''
        raise RuntimeError(f'HTTP {e.code}: {msg}') from None


def modelos():
    res = pedir('GET', '/models?pageSize=200')
    encontrados = [m for m in res.get('models', []) if 'image' in m.get('name', '').lower()
                   and 'generateContent' in m.get('supportedGenerationMethods', [])]
    for m in encontrados:
        print(m['name'].replace('models/', ''), '|', m.get('displayName', ''))
    if not encontrados:
        print('La cuenta no tiene modelos de imagen con generateContent.')


def generar(prompt, salida, refs=None, aspecto=None, modelo=MODELO_POR_DEFECTO):
    partes = []
    for ruta in refs or []:
        tipo = mimetypes.guess_type(ruta)[0] or 'image/png'
        with open(ruta, 'rb') as f:
            partes.append({'inlineData': {'mimeType': tipo, 'data': base64.b64encode(f.read()).decode('ascii')}})
    partes.append({'text': prompt})
    config = {'responseModalities': ['IMAGE']}
    if aspecto:
        config['imageConfig'] = {'aspectRatio': aspecto}
    res = pedir('POST', f'/models/{modelo}:generateContent', {
        'contents': [{'role': 'user', 'parts': partes}],
        'generationConfig': config,
    })
    for cand in res.get('candidates', []):
        for p in cand.get('content', {}).get('parts', []):
            datos = p.get('inlineData') or p.get('inline_data')
            if datos and datos.get('data'):
                os.makedirs(os.path.dirname(os.path.abspath(salida)), exist_ok=True)
                with open(salida, 'wb') as f:
                    f.write(base64.b64decode(datos['data']))
                return salida
    motivo = (res.get('promptFeedback') or {}).get('blockReason') or \
        ((res.get('candidates') or [{}])[0].get('finishReason')) or 'sin imagen en la respuesta'
    raise RuntimeError(f'No se generó imagen ({motivo}).')


def main():
    ap = argparse.ArgumentParser()
    sub = ap.add_subparsers(dest='accion', required=True)
    sub.add_parser('modelos')
    g = sub.add_parser('generar')
    g.add_argument('--prompt', required=True)
    g.add_argument('--salida', required=True)
    g.add_argument('--ref', action='append', default=[])
    g.add_argument('--aspecto')
    g.add_argument('--modelo', default=MODELO_POR_DEFECTO)
    l = sub.add_parser('lote')
    l.add_argument('archivo')
    l.add_argument('--modelo', default=MODELO_POR_DEFECTO)
    a = ap.parse_args()

    if a.accion == 'modelos':
        modelos()
    elif a.accion == 'generar':
        print('OK', generar(a.prompt, a.salida, a.ref, a.aspecto, a.modelo))
    else:
        with open(a.archivo, encoding='utf-8') as f:
            trabajos = json.load(f)
        for i, t in enumerate(trabajos, 1):
            if os.path.exists(t['salida']) and not t.get('rehacer'):
                print(f'[{i}/{len(trabajos)}] ya existe, se omite: {t["salida"]}')
                continue
            for intento in range(3):
                try:
                    generar(t['prompt'], t['salida'], t.get('refs'), t.get('aspecto'), a.modelo)
                    print(f'[{i}/{len(trabajos)}] OK {t["salida"]}')
                    break
                except RuntimeError as e:
                    print(f'[{i}/{len(trabajos)}] intento {intento + 1}: {e}')
                    time.sleep(5 * (intento + 1))


if __name__ == '__main__':
    try:
        main()
    except RuntimeError as e:
        sys.exit(f'Error: {e}')
