@echo off
set builddir=C:\TEMP\IntB

echo Copying files into %builddir%...
rem echo del /f /q /s %builddir%\
rmdir /q /s %builddir%
mkdir %builddir%
xcopy /e /y . %builddir%

echo Clearing logs, tests and temporary files...
del /f /q /s %builddir%\logs\*.*
mkdir %builddir%\logs\visits
del /f /q /s %builddir%\template\def\counter_?.tpl
rmdir /q /s %builddir%\tmp
mkdir %builddir%\tmp
mkdir %builddir%\tmp\template
rmdir /q /s %builddir%\test
rmdir /q /s %builddir%\convertor
rmdir /q /s %builddir%\.settings
rmdir /q /s %builddir%\install
rmdir /q /s %builddir%\distr
del %builddir%\.*
copy .htaccess. %builddir%\.htaccess
del %builddir%\dump.sql
del %builddir%\yandex*.*
del %builddir%\google*.*
del %builddir%\make.bat
del %builddir%\favicon.ico
del %builddir%\www\install.php
copy install\install.php %builddir%\www\install.php

echo Clearing configs...
del %builddir%\etc\ib_config.php
del %builddir%\etc\htaccess.txt
del %builddir%\www\robots.txt
del %builddir%\www\.htaccess
rem copy %builddir%\www\htaccess.def %builddir%\www\.htaccess

echo Clearing avatars and attaches...
del /f /q /s %builddir%\www\f\av\*.*
copy www\f\av\no.jpg %builddir%\www\f\av\no.jpg
del /f /q /s %builddir%\www\f\cap\*.*
del /f /q /s %builddir%\www\f\ph\*.*
copy www\f\ph\no.jpg %builddir%\www\f\ph\no.jpg
del /f /q /s %builddir%\www\f\up\1\*.*
mkdir %builddir%\www\f\up\1\pr
mkdir %builddir%\www\f\up\1\pr\240x180
copy www\f\up\1\index.html %builddir%\www\f\up\1\index.html

echo Clearing styles...
for /d %%a in (%builddir%\www\s\*.) do if not %%a==%builddir%\www\s\def rmdir /s /q %%a
for /d %%a in (%builddir%\template\*.) do if not %%a==%builddir%\template\def rmdir /s /q %%a
echo Making installation dump...
mysql -u root -p1 ib_current < db\sql\clear.sql
mysqldump -u root -p1 ib_current -K --compact --add-drop-table > %builddir%\db\sql\mysqli_new.sql
mysqldump -u root -p1 ib_current -K --compact --add-drop-table > %builddir%\db\sql\mysql5_new.sql
echo Packing...
del intbpro.zip
del intbpro.7z
"C:\Program Files\7-Zip\7z.exe" a -r -ssc distr\intbpro.zip %builddir%\*
"C:\Program Files\7-Zip\7z.exe" a -r -ssc distr\intbpro.7z %builddir%\*
pause