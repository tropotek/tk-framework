
# TODO

- This lib should be a de-coupled as possible to use the libs without the framework....
- refactor the Form and Table libs to be as decoupled from all libs as possible
- Test if DB, Table, and Form can work without external libs



# After libs complete

- TkWiki: Refactor the Wiki site to work with new DB and libs
- TkTis: Look into porting over the jobsystem to the new libs
- APD: Start planning the refactor of the APD site to the new libs

# SMDC Club Site:
- Look into developing a new SMDC club site with the following features:
    - Members list with member login areas
    - Content pages, with permissions for public, members, and admins
    - Look into using facebook logins for users that are members of the SMDC facebook group (is this possible)
    - Allow payment of membership via paypal (also manual updating if paying by directDeposit, allow BTC in future)
    - Also admins sending of emails to all members with attachments
    - Add map with camp locations

# RelicHunter Web App:
Rebuild the the relic hunter app as a web app, loose the focus of having the GPS
positioning and rely more on the manual pinpointing of a find location, but keep the 
main featuires of the app:
- A Google map of all find locations
- export KMZ file
- import KMZ file
- item details: detectors, coils, depth, size, type, etc
- allow user to select store localy, server (later add web drives if possible)
- add share find to sms,facebook, etc
- look into a web app that can work offline (not sure what is needed for this)
