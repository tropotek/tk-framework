#CHANGELOG#

Ver 2.0.12 [2017-04-27]:
-------------------------------
 - Minor Code Updates
 - 252s-dev.vet.unimelb.edu.au
 - Fixed up Status object and handlerInterface
 - Added new supervisor table
 - Removed CompanCourse objects as they did not work as elegantly as expected


Ver 2.0.11 [2017-04-02]:
-------------------------------
 - Minor Code Updates


Ver 2.0.10 [2017-03-08]:
-------------------------------
 - Minor Code Updates
 - 252s-dev.vet.unimelb.edu.au


Ver 2.0.9 [2017-02-23]:
-------------------------------
 - Minor Code Updates


Ver 2.0.8 [2017-02-22]:
-------------------------------
 - Fixed up the code with new lib updates
 - Added new change password system
 - Fixed recover password system
 - Minor Code Updates
 - 252s-dev.vet.unimelb.edu.au
 - Finished Beta Plugin System


Ver 2.0.7 [2017-01-20]:
-------------------------------
 - Finalising Table column select action


Ver 2.0.6 [2016-12-30]:
-------------------------------
 - Minor Code Updates
 - 252s-dev.vet.unimelb.edu.au


Ver 2.0.5 [2016-11-11]:
-------------------------------
 - Minor Code Updates
 - 252s-dev.vet.unimelb.edu.au
 - Merge branch 'master' of https://github.com/tropotek/tk-framework
 - That will do fine
 - Deprecated debug lib in favor of the framework \Tk\Debug objects and new framework functions folder
 - Updated status ribbons
 - Added attachments to contact form
 - Added new Color object
 - Updated Public content pages
 - Fixed course copy method
 - Added screenshots
 - Added OrderBy sortable javascript
 - Started new comment rating sections
 - Fixed some url path issues, added PageBytes Dom Filter
 - Updated the edit project and files page
 - Finished the Project Listing
 - Fixed intitution edit, cron job script


Ver 2.0.4 [2016-10-04]:
-------------------------------


Ver 2.0.3 [2016-09-05]:
-------------------------------
 - Updated Plugin system
 - Fixed Less Compiler
 - Cleaned config
 - Fixed Select with no value in array
 - Modified user management in administration
 - Added new tk mail lib
 - Implemented lib into project
 - Checkbox broken still fixing it!
 - Updated Form File field and javascript plugin
 - Updated Edit Institution page
 - Changed all = [] to = array()
 - Change php version check to gt php5.0.0
 - Fixed DB session interface
 - Updated Encode object


Ver 2.0.2 [2016-07-11]:
-------------------------------


Ver 2.0.1 [2016-07-10]:
-------------------------------
 - Added tinymce to edit page...
 - Added basic view and edit controllers for wiki pages
 - Finished user login, register
 - Started Wiki pages and routing
 - More wiki updates, updated the Auth adapters too
 - Finalised base template site
 - Finished routing, added new template to site
 - Finished basic HTTP objects
 - Finalised Basic Routing, Started KErnal
 - Added new files to the new http lib
 - Fixed Url to Uri class names and methods
 - Started to implement PSR7 interfaces, this will break most things using the URL
 - Updated code, added an update.md with info for the updated codes...
 - Finished draft of student rotation select grid
 - Finalised staff rotation manager/editor table.
 - Started rotation creation javascript plugin
 - Merge branch 'master' of git://github.com/tropotek/tk-framework


Ver 2.0.0 [2016-04-19]:
-------------------------------
 - Finalised base code for uni apps
 - Finally added tabs and fieldsets to Form renderer
 - Fixed LTI classes and logic
 - Fixed date timezone issues with timestamp use \Tk\Date::parse({timestamp})
 - Adde Lti Authentication, still need finishing....
 - Fixed Auth system, started LTI connector setup
 - LDAP and authentication tidy up
 - Updated form calls to new form
 - Finished basic user system.
 - Updated form, relized it needs to be refactored, see readme
 - Merge branch 'master' of git://github.com/tropotek/tk-framework
 - Modded DB things, Made MyuSQL ANSI mode false by default
 - Added postgress compatable queries to the \Tk\PDo object
 - Fixed some DB queries
 - Minor DB updates
 - Updated DB libs for postgress compatability
 - Tiding up
 - Seperating symfony from tk-framework adding to App
 - Fixing DomTemplate documentation
 - Finished table and form, need to create updated extrnal libs now
 - Added Filter form to table.
 - Minor updates
 - More work on the table and filter
 - Added minor table updates, nearly there
 - Bringing back the DB ArrayObject and Tool... Bigger, Better and Cleaner than ever!!!!!
 - Back to stuffing with the lower DB objects...grrrr
 - Back to screwing with the DB objects again, I think we need the TkTool back
 - Base of new Table lib complete
 - Started working on the new Table lib
 - I think I finally am happy with the Form base objects, still have to do a straight HTML renderer
   tho....
 - Finished form re-design
 - Still working on the form lib
 - Added file type to form lib
 - Finalised inline renderer
 - Added get/set renderer to form
 - More form stuff
 - Added edit form page for testing
 - Added sb-admin2 template for admin and fixed URL redirect code to 302
 - Added Authentication and Form objects
 - Added new Auth system for development
 - Db updates
 - Finiished base DB object
 - Fixed conflict
 - Update comments
 - Documentation stuff
 - Update douc comments
 - More updates
 - Minor updates
 - Minor update
 - Updated the framework a little, tis all...
 - Updating framework v2
 - Remove v1 files and added v2 Files


Ver 1.2.10 [2015-06-18]:
-------------------------------
 - Fixed no results in Student Assessmenet table.


Ver 1.2.9 [2015-03-18]:
-------------------------------
 - Try this then
 - Fixed SSL redirect issue. Pat this one is for you
 - Started working on youtube upload script and API implementation
 - Started adding the youtube processing script. Updated media view codes


Ver 1.2.8 [2015-02-10]:
-------------------------------
 - Added goals Manager for asier location of GOALS submissions, Remove menu auto open on hover, it was
   not an elegant solution


Ver 1.2.7 [2015-01-20]:
-------------------------------
 - Added \Tk\Template PHPUnit test


Ver 1.2.6 [2015-01-15]:
-------------------------------
 - Bug: Fixed Mail template block isse where some blocks where being replaced with 1 instead of being
   shown


Ver 1.2.5 [2015-01-14]:
-------------------------------
 - Finalising igal for first release.


Ver 1.2.4 [2014-12-09]:
-------------------------------
 - Added GOALS view action to student view
 - Moved Dashboard Status boxes to Term Dashboard


Ver 1.2.0 [2014-11-17]:
----------------
 - Finished updates
 - Main sectiosn of the GOALS question manager is completed.
 - Implemented sorting to question list
 - Started adding new GOALS question manager
 - Started updated to GOALS questions ordering
 - Changes after github migration
 - Fixed javascript files after removal of jquery in assets dir


Ver 1.1.9 [2013-12-13]:
----------------
 - Updated company description fields
 - Fixed staff manager
 - Fixed Category edit redirect
 - FixedPlacement map
 - Added First Four email block
 - Fixed historic placement validation check
 - Added new basic template object
 - Updated placement email system to use {blocks}{/blocks] in mail templates
 - Fixed attachement issues
 - Updated ltiSession object for use with individual consumerKeys in saving settings to db
 - Added hook to Gateway.php preCheckReferer
 - Fixed html/text message format in mail gateway
 - Fixed mailer on trunk


Ver 1.1.8 [2013-12-13]:
----------------
 - Added new basic template object
 - Updated placement email system to use {blocks}{/blocks] in mail templates
 - Fixed attachement issues
 - Updated ltiSession object for use with individual consumerKeys in saving settings to db
 - Added hook to Gateway.php preCheckReferer
 - Fixed html/text message format in mail gateway
 - Fixed mailer on trunk
 - Fixed email issues when running from a cron/bin file
 - Added option to dissable the auto-approve engine for courses
 - Added option to remove supervisor field for comapnies and placements
 - Clean up any var dumps no loner used
 - Tested placement system for new Ag students


Ver 1.1.7 [2013-12-13]:
----------------
 - Fixed attachement issues
 - Updated ltiSession object for use with individual consumerKeys in saving settings to db
 - Added hook to Gateway.php preCheckReferer
 - Fixed html/text message format in mail gateway
 - Fixed mailer on trunk
 - Fixed email issues when running from a cron/bin file
 - Added option to dissable the auto-approve engine for courses
 - Added option to remove supervisor field for comapnies and placements
 - Clean up any var dumps no loner used
 - Tested placement system for new Ag students
 - Finished basic LTI integration


Ver 1.1.6 [2013-12-13]:
----------------
 - Fixed email issues when running from a cron/bin file
 - Added option to dissable the auto-approve engine for courses
 - Added option to remove supervisor field for comapnies and placements
 - Clean up any var dumps no loner used
 - Tested placement system for new Ag students
 - Finished basic LTI integration
 - Fixed date
 - Some minor updates
 - Tag: Restore trunk composer.json
 - Tag: Updated changelog.md file for tag: 1.1.2
 - Tag: Updated composer.json for tag release
 - Minor updates
 - Tag: Updated changelog.md file for tag: 1.1.1


Ver 1.1.5 [2013-12-13]:
----------------
 - Fixed email issues when running from a cron/bin file
 - Added option to dissable the auto-approve engine for courses
 - Added option to remove supervisor field for comapnies and placements
 - Clean up any var dumps no loner used
 - Tested placement system for new Ag students
 - Finished basic LTI integration
 - Fixed date
 - Some minor updates
 - Tag: Restore trunk composer.json
 - Tag: Updated changelog.md file for tag: 1.1.2
 - Tag: Updated composer.json for tag release
 - Minor updates
 - Tag: Updated changelog.md file for tag: 1.1.1
 - Fixed email var


Ver 1.1.4 [2013-12-13]:
----------------
 - Finished basic LTI integration
 - Fixed date
 - Some minor updates
 - Tag: Restore trunk composer.json
 - Tag: Updated changelog.md file for tag: 1.1.2
 - Tag: Updated composer.json for tag release
 - Minor updates
 - Tag: Updated changelog.md file for tag: 1.1.1
 - Fixed email var
 - Fixed Mail Message links
 - Added Files To Comments
 - Fixed Form file field javascript
 - Updated goals reporter studentNumber filter
 - Fixed login system


Ver 1.1.3 [2013-12-13]:
----------------
 - Some minor updates
 - Tag: Restore trunk composer.json
 - Tag: Updated changelog.md file for tag: 1.1.2
 - Tag: Updated composer.json for tag release
 - Minor updates
 - Tag: Updated changelog.md file for tag: 1.1.1
 - Fixed email var
 - Fixed Mail Message links
 - Added Files To Comments
 - Fixed Form file field javascript
 - Updated goals reporter studentNumber filter
 - Fixed login system
 - Updated DB toatal count query
 - Updating Project for new theme
 - Removing theme folder
 - Added filters to GOALS plugin
 - Updated Import System
 - Added new Multi Select javascript
 - Fixec Table CSV Action to handle large data blocks
 - Fixed Mail template manager and editor
 - Modified mail template edit urls
 - Added pending on historic placements
 - Fixed minor UI issues on Placement Manager and Mail log.
 - Fixed mail log pagenation.
 - Added import to goals plugin
 - Fixed Crumbs bug
 - Added new makeDocs cli command for compiling project documnetation
 - Finished fixing crumbs
 - Added changerlog


Ver 1.1.2 [2013-12-13]:
----------------
 - Tag: Updated composer.json for tag release
 - Minor updates
 - Tag: Restore trunk composer.json
 - Tag: Updated changelog.md file for tag: 1.1.1
 - Fixed email var
 - Fixed Mail Message links
 - Added Files To Comments
 - Fixed Form file field javascript
 - Updated goals reporter studentNumber filter
 - Fixed login system
 - Updated DB toatal count query
 - Updating Project for new theme
 - Removing theme folder
 - Added filters to GOALS plugin
 - Updated Import System
 - Added new Multi Select javascript
 - Fixec Table CSV Action to handle large data blocks
 - Fixed Mail template manager and editor
 - Modified mail template edit urls
 - Added pending on historic placements\nFixed minor UI issues on Placement Manager and Mail
   log.\nFixed mail log pagenation.
 - Added import to goals plugin
 - Fixed Crumbs bug
 - Added new makeDocs cli command for compiling project documnetation
 - Finished fixing crumbs
 - Added changerlog


Ver 1.1.1 [2013-12-13]:
----------------
 - Tag: Updated composer.json for tag release
 - Fixed email var
 - Fixed Mail Message links
 - Added Files To Comments
 - Fixed Form file field javascript
 - Updated goals reporter studentNumber filter
 - Fixed login system
 - Updated DB toatal count query
 - Updating Project for new theme
 - Removing theme folder
 - Added filters to GOALS plugin
 - Updated Import System
 - Added new Multi Select javascript
 - Fixec Table CSV Action to handle large data blocks
 - Fixed Mail template manager and editor
 - Modified mail template edit urls
 - Added pending on historic placements\nFixed minor UI issues on Placement Manager and Mail
   log.\nFixed mail log pagenation.
 - Added import to goals plugin
 - Fixed Crumbs bug
 - Added new makeDocs cli command for compiling project documnetation
 - Finished fixing crumbs
 - Added changerlog



