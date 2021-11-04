# Welcome to the Zinecat Imports documentation! 
This document will teach you how to add a set of zine catalog data to the Zinecat database through the CollectiveAccess platform. It will cover the technical workflow of interacting with CollectiveAccess, as well as the project norms/standards of what fields should be added to Zinecat, and where they should go.

- Tools: Excel, Terminal (or other command line access), CollectiveAccess

## Backing up the existing database (CollectiveAccess, Terminal)

The first step is to make sure that you back up the existing CA database, in the event that your import isn’t quite correct and will need to be rolled back, tweaked, and re-ingested. 
First, you will need to remotely connect to the ZineCat development server using SSH (Secure SHell) in your Terminal. Open up Terminal and type the following:

    ssh myUsername@zinecatdev.openflows.com

Terminal should ask you for your password; type it and hit enter.
You should see something like this in your Terminal: 

    Linux zinecatdev 4.19.0-14-amd64 #1 SMP Debian 4.19.171-2 (2021-01-30) x86_64

    The programs included with the Debian GNU/Linux system are free software; the exact distribution terms for each program are described in the individual files in /usr/share/doc/*/copyright.

    Debian GNU/Linux comes with ABSOLUTELY NO WARRANTY, to the extent permitted by applicable law.
    Last login: Wed Apr 21 15:51:46 2021 from 68.9.34.247
    cassandra@zinecatdev:~$

Now you’re connected to the server! We need to back up the db + zip it into a compressed file that we will be able to restore from later. Type the following:

    sudo mysqldump zinecat | gzip -v > ~/filename.sql.gz

This command will take a little while to run. While it’s working, we can begin on the next step.
## Mapping the catalog data to Zinecat’s database (Excel)

The next step is to figure out how to take the existing catalog dataset and translate it into Collective Access’ data model. This is the most subjective and fiddly step of the process.

A data map is simply a spreadsheet containing each field of incoming data, and each field that it is to be transferred into in Collective Access. Collective Access has some predefined variables and settings that are used in this process.

Here are a few of the most common Collective Access headings that you might see working with zine catalog data: 


| Incoming data  | Collective Access data |
| ------------- | ------------- |
| Zine title  | ca_objects.preferred_labels  |
| Subject headings  | ca_objects.subject  |
| Description/summary of zine  | ca_objects.description  |
| Author  | ca_entities  |
| Date of publication | ca_objects.date.dates_value |


Check out the [MAPPING CHEAT SHEET](https://docs.google.com/spreadsheets/d/1xSLdt6H44u7obG3iNpHjyRiZeOs1gzJ-Ze--HwUx9gI/edit?usp=sharing) or some of the examples of pre-existing mapping files to get a more comprehensive sense of how this works.
## Importing the mapped catalog data (CollectiveAccess)

Once your mapping looks correct, it’s time to try ingesting your catalog data. In the CA interface, go to Import > Data. Drag your mapping Excel file into the box to add it to the list. Then click the arrow in the right hand column of your new mapping to begin a new import. In the “Data File” section on the next screen, browse to your data Excel file. Then click “Execute data import.” Note that this may take some time, and your screen may appear to be frozen for awhile. You may or may not get an “in progress” loading screen, depending on how long the ingest is taking.

## Check the import for errors & restore backup if needed (CollectiveAccess, Terminal)

Now we get to find out how well our mapping actually worked. Once the ingest finishes, go back to your [admin dashboard](https://zinecatdev.openflows.com/admin/) and try searching for any zine in your dataset. Make sure that:
- The zine actually shows up when you search for it by title
- When you click on the zine and go to “Basic xZinecorex Info”, all of the fields that you added in your mapping Excel sheet are present and accounted for.

There’s a decent chance that #2 will not be true the first time you run the ingest. If so, it’s time to roll back the database to the backup you made earlier so that you can revisit the mapping and try again. Before doing anything, note down which fields are missing so that you can change them in your mapping file. Then, run the following in your Terminal:

    pv ~/filename.sql.gz | gunzip | sudo mysql zinecat

You should see a progress bar in your Terminal indicating the progress of the rollback, which may take a little while. As it’s running, you can open up your mapping Excel sheet and look at the missing/incorrect fields that you identified within Zinecat. Double check that you have the right options in the CA table.element column and the Refinery + Refinery Parameters columns - these are some of the more common places where errors can occur. Cross reference with the Collective Access Documentation and consult other members of the Zinecat team where necessary. [This reference](https://zinecatdev.openflows.com/admin/index.php/administrate/setup/interface_screen_editor/InterfaceScreenEditor/Edit/screen_id/33) is particularly useful - try hovering over the elements on this screen to get more information about different table.elements. 

Once the database is finished rolling back (as indicated by your Terminal), repeat step #3 again. You may need to ingest, check for missing fields, roll back, and ingest again several times before your data is completely correct.
