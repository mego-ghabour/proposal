import urllib.request

whatsapp_ua = "WhatsApp/2.23.20.76 A"

url = "https://fkretk.com/proposal/cosmetics?=v2"
req = urllib.request.Request(url, headers={
    'User-Agent': whatsapp_ua,
    'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    'Accept-Language': 'ar,en-US;q=0.7,en;q=0.3'
})

try:
    with urllib.request.urlopen(req, timeout=10) as resp:
        print("Status:", resp.status)
        print("Headers:", dict(resp.headers))
        body = resp.read(2000).decode('utf-8', errors='ignore')
        print("First 1500 chars of body:\n", body[:1500])
except Exception as e:
    print("Error:", e)
