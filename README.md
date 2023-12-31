# Vici.org

Codebase for [https://vici.org)](https://vici.org).

## Installation

To run this on a local machine, you need a database (MariaDB / MySQL) and a webserver (I use nginx) and PHP (should work on 7.4). See the included nginx config for details.

In `/db` you'll find sql dumps of two databases:
* `geo.sql.gz`
* `vici.sql.gz`

`geo.sql` is used for the reverse geocoder, that displays the name of the location on the `/vici/{item}` pages. This data is derived from geonames. This dump is not the complete database, it only includes places from a few European countries, like DE, NL, BE, IT, FR, ES.

`vici.sql` is a complete (October 2023) dump of all data from vici.org, with anonymized used data.

Load both into MariaDB / MySQL.

The vici.org PHP codes gets the required credentials from environment variables. See `fastcgi_params` in the nginx folder for an example. 

If set, the environment variable `VICIBASE`, is passed to the `vici.js` javascript widget. This will use it for the base url for data requests it needs to make. So if your local instance runs as http://vici.local, set `VICIBASE` accordingly.

## Tiles

The tiles used in Vici.org are served by [tileproxy](https://github.com/renevoorburg/tileproxy).

## Images

The images served by Vici.org are processed and cached by [imageserver](https://github.com/renevoorburg/imageserver).


