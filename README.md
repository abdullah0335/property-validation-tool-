# PropVal — UK Property Valuation Tool

Aggregates property price data from **Rightmove**, **Zoopla**, and **OnTheMarket**
to calculate an average valuation for any UK location.

---

## Features

- Search by UK postcode or location name
- Filter by property type (detached, flat, terraced, etc.) and bedrooms
- Displays average price per portal with min/max range and £/sq ft estimate
- Visual price comparison bar chart
- Sample listing cards per portal
- Response caching (1 hour per location/filter combo)

---

## Requirements

| Requirement | Version |
|-------------|---------|
| PHP | 7.4+ (8.x recommended) |
| cURL extension | enabled |
| Apache / Nginx | any modern version |

---

## Setup

### 1. Clone / copy files

```
/your-webroot/
├── index.php
├── api/
│   └── valuation.php
├── assets/
│   ├── css/style.css
│   └── js/app.js
├── cache/             ← auto-created by PHP (must be writable)
└── README.md
```

### 2. Set folder permissions

```bash
chmod 755 /your-webroot/
chmod -R 644 /your-webroot/assets/
chmod 775 /your-webroot/cache/     # PHP needs to write cache files
```

### 3. Apache – no extra config needed

If you use a `.htaccess`, make sure `AllowOverride All` is set.

### 4. Nginx config snippet

```nginx
server {
    listen 80;
    server_name propval.yourdomain.com;
    root /your-webroot;
    index index.php;

    location / { try_files $uri $uri/ /index.php$is_args$args; }
    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
    location /cache { deny all; }
}
```

---

## How the scraper works

`api/valuation.php` sends browser-like HTTP requests (via cURL) to each portal's
public search page and attempts to parse prices from the embedded JSON:

| Portal | Data source |
|--------|-------------|
| Rightmove | `window.jsonModel` JSON blob in HTML |
| Zoopla | `__NEXT_DATA__` Next.js JSON blob |
| OnTheMarket | `application/ld+json` schema blocks |

If live scraping is blocked or returns no prices, the system falls back to
**realistic simulated prices** seeded by the location name so results are
consistent across requests.

---

## Production recommendations

### Use a scraping proxy
Portals use bot-detection (Cloudflare, DataDome). Integrate a proxy service:

```php
// In http_get(), add:
CURLOPT_PROXY => 'http://user:pass@proxy.brightdata.com:22225',
```

Popular options:
- **Bright Data** (residential proxies — best success rate)
- **ScraperAPI** (simple API wrapper)
- **Oxylabs**

### Cache in a database
Replace file-based cache with Redis or MySQL for scalability:

```php
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);
$cached = $redis->get("propval:{$cacheKey}");
```

### Use a paid property data API
For guaranteed, accurate data replace scraping with:
- **Land Registry Price Paid Data** (free, historical)
- **PropertyData API** (live averages by postcode)
- **Hometrack / Dataloft** (enterprise)
- **Zoopla AVM API** (if you have access)

---

## API Endpoint

```
GET api/valuation.php?location=SW1A+1AA&property_type=flat&bedrooms=2
```

**Parameters:**

| Param | Required | Values |
|-------|----------|--------|
| `location` | yes | Any UK postcode or area name |
| `property_type` | no | `any`, `detached`, `semi-detached`, `terraced`, `flat`, `bungalow` |
| `bedrooms` | no | `0` (any) through `5` |

**Response (JSON):**

```json
{
  "location": "Mayfair, London",
  "property_type": "flat",
  "bedrooms": 2,
  "overall_average": 1850000,
  "vs_national_pct": 537.9,
  "portals": {
    "rightmove":   { "status": "ok", "count": 12, "average": 1900000, "min": 950000, "max": 3200000, "per_sqft": 2111, "listings": [...] },
    "zoopla":      { "status": "ok", "count": 9,  "average": 1820000, ... },
    "onthemarket": { "status": "ok", "count": 7,  "average": 1830000, ... }
  },
  "timestamp": "2024-09-01T12:00:00+00:00"
}
```

---

## Legal note

Scraping publicly visible property listings is generally permitted for
personal/research use in the UK, but commercial use may require permission
from each portal. Always respect each site's `robots.txt` and Terms of Service.
