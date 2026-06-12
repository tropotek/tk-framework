#CHANGELOG#

Ver 8.0.136 [2026-06-12]:
-------------------------------
  - fixed invoice search added students and total row to invoice export


Ver 8.0.134 [2026-04-25]:
-------------------------------


Ver 8.0.132 [2026-03-17]:
-------------------------------
  - update docker
  - Fix page title and icons


Ver 8.0.130 [2025-12-24]:
-------------------------------
  - Added content snippet templates to reporting fields


Ver 8.0.128 [2025-12-22]:
-------------------------------
  - Added all requests from client, ready for UA testing
  - Added docker container and install scripts


Ver 8.0.126 [2025-12-13]:
-------------------------------
  - fix data map
  - Merge branch '8.0' of https://github.com/tropotek/tk-framework into 8.0


Ver 8.0.124 [2025-12-08]:
-------------------------------


Ver 8.0.122 [2025-11-29]:
-------------------------------
  - Fix domain ping bytes DB column size


Ver 8.0.120 [2025-11-27]:
-------------------------------


Ver 8.0.118 [2025-11-21]:
-------------------------------
  - Added product price bulk update
  - Merge branch '8.0' of https://github.com/tropotek/tk-framework into 8.0


Ver 8.0.116 [2025-11-16]:
-------------------------------


Ver 8.0.114 [2025-11-15]:
-------------------------------
  - fix model mapper
  - phpstan fixes
  - Update DB Mapper


Ver 8.0.112 [2025-10-09]:
-------------------------------
  - clean empty encrypted fields


Ver 8.0.110 [2025-09-30]:
-------------------------------
  - Major refactor of DomTemplate lib


Ver 8.0.108 [2025-08-19]:
-------------------------------
  - implement new safe enc for all
  - update enc class
  - Renamed Dispatch class to Listeners
  - Merge branch '8.0' of https://github.com/tropotek/tk-framework into 8.0


Ver 8.0.106 [2025-08-16]:
-------------------------------


Ver 8.0.104 [2025-08-16]:
-------------------------------
  - Removed unneeded libs


Ver 8.0.102 [2025-08-16]:
-------------------------------


Ver 8.0.100 [2025-08-01]:
-------------------------------


Ver 8.0.98 [2025-07-19]:
-------------------------------
  - Update all tables


Ver 8.0.96 [2025-07-10]:
-------------------------------


Ver 8.0.94 [2025-07-08]:
-------------------------------


Ver 8.0.92 [2025-07-07]:
-------------------------------


Ver 8.0.90 [2025-07-06]:
-------------------------------


Ver 8.0.88 [2025-07-04]:
-------------------------------
  - masive cache object cleanup


Ver 8.0.86 [2025-07-02]:
-------------------------------
  - Added profile photo component


Ver 8.0.84 [2025-06-21]:
-------------------------------
  - update notifications and recipients


Ver 8.0.82 [2025-06-10]:
-------------------------------
  - convert all dialog components
  - Add table exception class
  - Update to use new Model object
  - Fix fin year report date select
  - Update Model class with find functions


Ver 8.0.80 [2025-05-26]:
-------------------------------
  - Merge branch '8.0' of https://github.com/tropotek/tk-framework into 8.0


Ver 8.0.78 [2025-05-22]:
-------------------------------
  - disable clear cache for components


Ver 8.0.76 [2025-05-22]:
-------------------------------
  - Fix cache object and cache hostname on first http


Ver 8.0.74 [2025-05-19]:
-------------------------------
  - Added BCC to config to receive copy of system emails


Ver 8.0.72 [2025-05-19]:
-------------------------------


Ver 8.0.70 [2025-05-18]:
-------------------------------


Ver 8.0.68 [2025-05-18]:
-------------------------------
  - Update to use static methods for Registry and Config
  - base lib cleanup, Config/Registry/Cache


Ver 8.0.66 [2025-05-08]:
-------------------------------
  - added report emails


Ver 8.0.64 [2025-04-21]:
-------------------------------


Ver 8.0.62 [2025-04-20]:
-------------------------------


Ver 8.0.60 [2025-04-20]:
-------------------------------
  - Added qr-code reader/scanner


Ver 8.0.58 [2025-04-20]:
-------------------------------
  - Added migration cli cmd
  - Merge branch '8.0' of https://github.com/tropotek/tk-framework into 8.0
  - Added hx files componenet
  - Added weight field calc
  - Added Client component
  - Added path case manager and filters
  - Add final models and tables
  - Added the bloody TOC code
  - Added students and updated settings
  - update all HTMX components
  - fix template logging
  - Added storage locations
  - Fix breadcrumbs
  - Added mail log system
  - Fix migration error messages
  - remove old Crumbs object
  - Add checkSelect field and persistand form fields
  - update form select options
  - Updated table and action classes
  - Added orderBy table cell


Ver 8.0.56 [2024-11-08]:
-------------------------------
  - Update date fields for objects
  - Updated Model to handle DateTimeImmutable


Ver 8.0.54 [2024-11-04]:
-------------------------------


Ver 8.0.52 [2024-11-03]:
-------------------------------
  - Update Uri to extend Psr\Http\Message\UriInterface
  - phpstan lvl7


Ver 8.0.50 [2024-10-14]:
-------------------------------
  - main src cleanup
  - fix secret edit page
  - phpstan lvl6 compliance
  - Added event dispatcher to Tk libs
  - Update discover baseUrl code
  - refactor Auth classes
  - Added browser notification system
  - Added microsoft and google SSO OAuth


Ver 8.0.48 [2024-09-25]:
-------------------------------
  - cleanup config methods
  - Added guest token system
  - refactor user reg and Uri init
  - implement new auth and user system
  - refactor MVC objects


Ver 8.0.46 [2024-09-16]:
-------------------------------
  - refactor form mapping and csrf token
  - Implement csrf token on forms


Ver 8.0.44 [2024-09-14]:
-------------------------------


Ver 8.0.42 [2024-09-14]:
-------------------------------
  - add email exception listener


Ver 8.0.40 [2024-09-14]:
-------------------------------
  - fix db session expiry
  - Merge branch '8.0' of https://github.com/tropotek/tk-framework into 8.0
  - Move Factory update System and registry
  - Update user config location


Ver 8.0.38 [2024-09-10]:
-------------------------------
  - Use hash in place of secret ID
  - Added sessions manager admin page


Ver 8.0.36 [2024-09-09]:
-------------------------------
  - fix menu edit and page select dialog


Ver 8.0.34 [2024-09-08]:
-------------------------------
  - Update libs with new table object
  - migrate new objects to libs
  - fix dbfilter for managers
  - fixed select dialogs
  - finish upgrading libs
  - update user management
  - Merge branch '8.0' of https://github.com/tropotek/tk-framework into 8.0
  - WIP
  - Update dom template ready to MVC code
  - remove monolog calls
  - minimise symfony request usage
  - Implement php native session
  - Update libs and site to use new DbModel, Form and Table objects
  - Update user manager, add new Bs table object
  - Added Bs\Table with filter form
  - added actions and orderby to new Tt table
  - Fix migrate cmd
  - Make DB static
  - add new table objects


Ver 8.0.32 [2024-04-16]:
-------------------------------
  - Fix wiki menu edit page_id api error
  - Added new Dom and Std form renderers


Ver 8.0.30 [2023-09-28]:
-------------------------------
  - Merge branch '8.0' of https://github.com/tropotek/tk-framework into 8.0


Ver 8.0.28 [2023-08-15]:
-------------------------------


Ver 8.0.26 [2023-08-15]:
-------------------------------
  - Update ObjectUril to get value from accessors


Ver 8.0.24 [2023-08-09]:
-------------------------------
  - Update data mapper objects


Ver 8.0.22 [2023-08-09]:
-------------------------------
  - removed table map requirement
  - Merge branch '8.0' of https://github.com/tropotek/tk-framework into 8.0
  - Added prepared stmt to filter functions


Ver 8.0.20 [2023-07-30]:
-------------------------------


Ver 8.0.18 [2023-07-16]:
-------------------------------
  - Update Edit and manager interfaces


Ver 8.0.16 [2023-07-09]:
-------------------------------


Ver 8.0.14 [2023-07-08]:
-------------------------------


Ver 8.0.12 [2023-07-06]:
-------------------------------


Ver 8.0.10 [2023-07-04]:
-------------------------------


Ver 8.0.8 [2023-07-03]:
-------------------------------


Ver 8.0.6 [2023-06-28]:
-------------------------------


Ver 8.0.4 [2023-06-28]:
-------------------------------


Ver 8.0.2 [2023-06-27]:
-------------------------------
  - Added menu editor
  - saving progress
  - Added breadcrumb object
  - Merge branch '8.0' of https://github.com/tropotek/tk-framework into 8.0
  - Added maintenance and form tabs
  - Cleaned up File object
  - cleanup remember me code
  - Added new alert object for flash messages
  - Added account recover page
  - Added form params back in
  - update requirements to PHP 8.1
  - Finished basic form functionality and fields


Ver 8.0.0 [2022-10-20]:
-------------------------------
  - updating
  - Updated PDO compatability
  - Adding Console commands
  - Updated file fo new 7.0 version of the tk-framework

