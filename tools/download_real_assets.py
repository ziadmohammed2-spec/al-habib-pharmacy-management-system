import json
import os
import ssl
import time
import urllib.parse
import urllib.request
import urllib.error


ROOT = os.path.abspath(os.path.join(os.path.dirname(__file__), ".."))
API = "https://commons.wikimedia.org/w/api.php"
CTX = ssl._create_unverified_context()

ASSETS = [
    {"title": "File:Panadol.jpg", "path": "assets/images/products/panadol.jpg", "width": 1200},
    {"title": "File:Redoxon Double Action Vitamin C standard tablets.jpg", "path": "assets/images/products/vitamin-c-500mg.jpg", "width": 900},
    {"title": "File:Amoxicillin 500mg capsules on a plate (Sandoz).jpg", "path": "assets/images/products/amoxicillin-500mg.jpg", "width": 1200},
    {"title": "File:Ibuprofen 400.jpg", "path": "assets/images/products/ibuprofen-400mg.jpg", "width": 1200},
    {"title": "File:Bayer Aspirin Pills.jpg", "path": "assets/images/products/aspirin.jpg", "width": 1200},
    {"title": "File:Cetirizin.jpg", "path": "assets/images/products/cetirizine.jpg", "width": 900},
    {"title": "File:Azithromycin 250mg.jpg", "path": "assets/images/products/azithromycin.jpg", "width": 1200},
    {"title": "File:Omeprazole 20mg.jpg", "path": "assets/images/products/omeprazole.jpg", "width": 1200},
    {"title": "File:Ventolin Inhaler N 100 ug inh.jpg", "path": "assets/images/products/ventolin-inhaler.jpg", "width": 900},
    {"title": "File:Siofor 500 mg tbl.jpg", "path": "assets/images/products/metformin-500mg.jpg", "width": 1200},
    {"title": "File:Lisinopril 20 mg.jpg", "path": "assets/images/products/lisinopril-20mg.jpg", "width": 1200},
    {"title": "File:Doxycycline 100mg capsules.jpg", "path": "assets/images/products/doxycycline-100mg.jpg", "width": 1000},
    {"title": "File:Capsules Spilling from Bottle (34356373370).jpg", "path": "assets/images/products/generic-capsules.jpg", "width": 1000},
    {"title": "File:Pill.svg", "path": "assets/images/placeholders/default-product.png", "width": 900},
    {"title": "File:Multicare pharmacist.jpg", "path": "assets/images/banners/hero-pharmacist.jpg", "width": 1400},
    {"title": "File:Pharmacy counter six, plus Influx of volunteers help keep Rader pharmacy at full speed 150831-A-DZ999-002.jpg", "path": "assets/images/banners/pharmacy-banner.jpg", "width": 1400},
    {"title": "File:ClearRx prescription bottles - Flickr - bartsz.jpg", "path": "assets/images/banners/upload-prescription.jpg", "width": 1200},
    {"title": "File:Man consults with pharmacist.jpg", "path": "assets/images/banners/contact-support.jpg", "width": 1200},
    {"title": "File:Shopping cart icon.svg", "path": "assets/images/placeholders/empty-cart.png", "width": 700},
    {"title": "File:Panadol.jpg", "path": "assets/images/categories/pain-relief.jpg", "width": 900},
    {"title": "File:Redoxon Double Action Vitamin C standard tablets.jpg", "path": "assets/images/categories/vitamins.jpg", "width": 900},
    {"title": "File:Amoxicillin 500mg capsules on a plate (Sandoz).jpg", "path": "assets/images/categories/antibiotics.jpg", "width": 900},
    {"title": "File:Omeprazole 20mg.jpg", "path": "assets/images/categories/digestive-care.jpg", "width": 900},
    {"title": "File:Dermatology products.jpg", "path": "assets/images/categories/personal-care.jpg", "width": 900},
    {"title": "File:A-first-aid-kit.jpg", "path": "assets/images/categories/first-aid.jpg", "width": 900},
    {"title": "File:Medicon medicine.svg", "path": "assets/images/icons/medicine.png", "width": 600},
    {"title": "File:Maki7-pharmacy.svg", "path": "assets/images/icons/pharmacy.png", "width": 600},
]


def fetch_json(url):
    request = urllib.request.Request(url, headers={"User-Agent": "AlHabibPharmacyAssetUpdater/1.0"})
    for attempt in range(5):
        try:
            with urllib.request.urlopen(request, context=CTX, timeout=60) as response:
                return json.loads(response.read().decode("utf-8"))
        except urllib.error.HTTPError as error:
            if error.code != 429 or attempt == 4:
                raise
            time.sleep(3 + attempt * 3)


def fetch_bytes(url):
    request = urllib.request.Request(url, headers={"User-Agent": "AlHabibPharmacyAssetUpdater/1.0"})
    for attempt in range(5):
        try:
            with urllib.request.urlopen(request, context=CTX, timeout=120) as response:
                return response.read()
        except urllib.error.HTTPError as error:
            if error.code != 429 or attempt == 4:
                raise
            time.sleep(3 + attempt * 3)


def image_info(title, width):
    params = {
        "action": "query",
        "format": "json",
        "prop": "imageinfo",
        "titles": title,
        "iiprop": "url|mime|extmetadata",
        "iiurlwidth": str(width),
    }
    data = fetch_json(API + "?" + urllib.parse.urlencode(params))
    pages = data.get("query", {}).get("pages", {})
    for page in pages.values():
        info = page.get("imageinfo", [])
        if info:
            return info[0]
    raise RuntimeError(f"No image info returned for {title}")


def extmetadata_value(info, key):
    value = info.get("extmetadata", {}).get(key, {}).get("value", "")
    return " ".join(value.replace("\n", " ").split())


def main():
    credits = [
        "# Local Image Credits",
        "",
        "Images were downloaded as local project assets from Wikimedia Commons using the listed file pages.",
        "Product mappings use the closest available real medicine/package photo for each medicine name or drug class.",
        "",
    ]

    for asset in ASSETS:
        info = image_info(asset["title"], asset["width"])
        download_url = info.get("thumburl") or info.get("url")
        if not download_url:
            raise RuntimeError(f"No download URL returned for {asset['title']}")

        target = os.path.join(ROOT, asset["path"].replace("/", os.sep))
        os.makedirs(os.path.dirname(target), exist_ok=True)
        with open(target, "wb") as handle:
            handle.write(fetch_bytes(download_url))

        license_name = extmetadata_value(info, "LicenseShortName") or "See source"
        artist = extmetadata_value(info, "Artist") or "Wikimedia Commons contributor"
        source_page = extmetadata_value(info, "DescriptionUrl") or info.get("descriptionurl", "")
        credits.append(f"- `{asset['path']}`: {asset['title']} by {artist}; license: {license_name}; source: {source_page}")
        print(f"Downloaded {asset['path']}")
        time.sleep(1)

    with open(os.path.join(ROOT, "ASSET_CREDITS.md"), "w", encoding="utf-8") as handle:
        handle.write("\n".join(credits) + "\n")


if __name__ == "__main__":
    main()
