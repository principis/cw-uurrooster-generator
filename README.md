# KUL CW uurrooster generator
Scrapes the [effectief uurrooster](https://eng.kuleuven.be/studeren/opleidingen/burgerlijk-ingenieur/master-computerwetenschappen/effectief-uurrooster) and turns the selected courses in a single ical file.

## Usage
This application is (at the moment) deployed [here](https://principis.be/cw_uurrooster/).

The selected courses are hashed with md5 and stored in the  sqlite database `short_links.db`. This is necessary to circumvent a Google Calendar URL length limit.

## Installation
```bash
# Clone the repo
git clone https://github.com/principis/cw-uurrooster-generator.git
cd cw-uurrooster-generator
# Install dependencies
composer install
# Create the short_links database
php short_links.php
# Setup a webserver and cron job...
```

## License
This project is licensed under the AGPL-3.0-or-later license. See the [license](LICENSE.md) file for details.