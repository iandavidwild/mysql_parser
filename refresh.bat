@ECHO off

SET inputpath=c:\Development\Upgrade\Production_data\fresh

ECHO username: %1 
ECHO inputpath: %inputpath%

SET /p password=Please enter password:

FOR %%f IN (%inputpath%\*.tmp) DO (
	ECHO Importing %%f
	mysql -u%1 -p%password% production < %%f
)
