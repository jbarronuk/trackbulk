# TrackBulk
[![Tests](https://github.com/jbarronuk/trackbulk/actions/workflows/tests.yml/badge.svg)](https://github.com/jbarronuk/trackbulk/actions/workflows/tests.yml)
[![Larastan](https://github.com/jbarronuk/trackbulk/actions/workflows/larastan.yml/badge.svg)](https://github.com/jbarronuk/trackbulk/actions/workflows/larastan.yml)

Bulk Royal Mail tracking for everyone. Upload a list of tracking numbers, hit go, and TrackBulk queries the Royal Mail Tracking API daily and reports the status of every parcel in one place.
 
Originally built as a small SaaS product, now released as open source so anyone can self-host it.
  
## Features
 
- **Bulk tracking** — paste in or upload a list of Royal Mail tracking numbers and get statuses for all of them in one go
- **Excel / CSV export** — export results to excel
- **User accounts** — register, log in, and keep your tracking history private
- **Stripe billing built in** — subscription billing via Laravel Cashier, ready to wire up if you want to run it as a paid service (or ignore entirely if self-hosting for yourself)

## Tech stack
 
- **Backend:** Laravel 11, PHP 8.2+
- **Frontend:** Vue 3 + Inertia.js, Tailwind CSS, Vite
- **Database:** MySQL
- **Integrations:** Royal Mail Tracking API, Stripe (Laravel Cashier)
## Getting started
 
### Prerequisites
 
- PHP 8.2+ and Composer
- Node.js 18+ and npm
- A database (MySQL, PostgreSQL, or SQLite all work)
- **Royal Mail Tracking API credentials** — you'll need to register for API access via the [Royal Mail developer portal](https://developer.royalmail.net/) and obtain a client ID and secret
- *(Optional)* A Stripe account if you want to enable billing

## License

## Acknowledgements
 
Built with [Laravel](https://laravel.com), [Vue](https://vuejs.org), [Inertia.js](https://inertiajs.com), and [Tailwind CSS](https://tailwindcss.com). Tracking data provided by the [Royal Mail Tracking API](https://developer.royalmail.net/).
 
> **Disclaimer:** This project is not affiliated with or endorsed by Royal Mail. "Royal Mail" is a trademark of Royal Mail Group Ltd.