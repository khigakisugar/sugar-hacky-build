# hacky-sugary-buildy
## About
A wrapper around existing build tools
I found the existing build tools, while good, lacked a bit in automation. Additionally, when needing demo data, 
the build process took longer than necessary. This script will manage demo data using mysqldump and mysql load file.
This also wraps around the existing build tools to make the interface a little easier.

## Included files

### build
This is an executable php script. It is intended to be the main entrypoint for the builder.
Usage: build [-f, --full] [-q, --quick]
Passing -f or --full will run a full build with the last version and flavor run. It will rebuild sidecar, build demo data from scratch, and store the demo data as a new mysqldump. Note that this flag will have precedence over -q/--quick
Passing -q or --quick will run a quick build with the last version and flavor run. It won't rebuild sidecar, and will load 
demo data from the appropriate mysqldump.
Passing nothing will cause the script to prompt the user for all options.

It is recommended that this script be run without options at the beginning of a project, so the user can explicitly set
their defaults for subsequent runs.

### config_si.php
This is the template for config_si in Mango/sugarcrm. This template is populated with values from the build script, and the
result is stored in Mango/sugarcrm

### phpstormrebuild.php
This script is to be run as an external script within phpstorm.  This script uses config_si.php to populate values for 
running inside of phpstorm. This way, you never have to edit or modify flags within phpstorm, and building from phpstorm will
always reflect changes that were done when building.

## Generated files

### *.sql
These are the mysqldump files, optionally generated during build. These can be reused to maintain data consistency and to speed subsequent builds.

### sugarbuildconfig.ini
This is automatically generated during the build process. It houses the default values, so it can remember previous user 
choices.
