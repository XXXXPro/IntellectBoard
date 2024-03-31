#!/bin/sh

BUILDDIR=/tmp/IntB
echo Copying files into $BUILDDIR...
rm -rf $BUILDDIR
mkdir $BUILDDIR
cp -R ./ $BUILDDIR

echo Clearing logs, tests and temporary files...
rm -rf $BUILDDIR/logs/*.*
mkdir -p $BUILDDIR/logs/visits
rm $BUILDDIR/template/def/counter_?.tpl
rm "$BUILDDIR/Intellect Board.phprj"
rm -rf $BUILDDIR/tmp
rm -rf $BUILDDIR/nbproject
mkdir -p $BUILDDIR/tmp
mkdir -p $BUILDDIR/tmp/template
rm -rf $BUILDDIR/test
rm -rf $BUILDDIR/convertor
rm -rf $BUILDDIR/.settings
rm -rf $BUILDDIR/install
rm -rf $BUILDDIR/distr
rm $BUILDDIR/.*
rm -rf $BUILDDIR/.git
cp .htaccess $BUILDDIR/.htaccess
rm $BUILDDIR/dump.sql
rm $BUILDDIR/www/yandex*.*
rm $BUILDDIR/www/google*.*
rm $BUILDDIR/www/mywot*.*
rm $BUILDDIR/www/robots.txt
rm $BUILDDIR/make.*
rm $BUILDDIR/favicon.ico
rm $BUILDDIR/www/install.php
rm $BUILDDIR/www/.htaccess
rm $BUILDDIR/template/def/counter_f.tpl
rm $BUILDDIR/template/def/counter_t.tpl
rm $BUILDDIR/template/def/counter_h.tpl
rm $BUILDDIR/modules/research.php
cp install/install.php $BUILDDIR/www/install.php
rm $BUILDDIR/etc/htaccess.txt
rm $BUILDDIR/etc/routes.txt
rm $BUILDDIR/etc/routes.cfg
rm $BUILDDIR/etc/ib_config.php
rm -rf $BUILDDIR/www/download
rm -rf $BUILDDIR/www/f/up/1
rm $BUILDDIR/www/f/instagram/*
rm $BUILDDIR/www/f/ph/*
mkdir $BUILDDIR/www/f/up/1
mkdir $BUILDDIR/www/f/up/1/pr
rm $BUILDDIR/www/fa/webfonts/*.svg
rm $BUILDDIR/Intellect\ Board*

echo Clearing avatars and attaches...
rm -rf $BUILDDIR/www/f/av/*.*
cp www/f/av/no.jpg $BUILDDIR/www/f/av/no.jpg
rm -rf $BUILDDIR/www/f/cap/*.*
rm -rf $BUILDDIR/www/f/ph/*.*
cp www/f/ph/no.jpg $BUILDDIR/www/f/ph/no.jpg
rm -rf $BUILDDIR/www/f/up/1/*.*
mkdir -p $BUILDDIR/www/f/up/1/pr
mkdir -p $BUILDDIR/www/f/up/1/pr/240x180
cp www/f/up/1/index.html $BUILDDIR/www/f/up/1/index.html

echo Clearing styles...
for a in $BUILDDIR/www/s/* ; do
 if [ $a != $BUILDDIR/www/s/def ]; then
  rm -rf $a
 fi
done

for a in $BUILDDIR/template/* ; do
 if [ $a != $BUILDDIR/template/def ]; then
  rm -rf $a
 fi
done

echo Making installation dump for MySQL...
mysql -u root -p1 ib_current < db/sql/clear.sql
mysqldump -u root -p1 ib_current -K --compact --add-drop-table | sed -e 's/ROW_FORMAT=FIXED//g' > $BUILDDIR/db/sql/mysqli_new.sql
mysqldump -u root -p1 ib_current -K --compact --add-drop-table | sed -e 's/ROW_FORMAT=FIXED//g' > $BUILDDIR/db/sql/mysql5_new.sql

echo Making installation dump for Postgres... 
echo Be sure Posgres is available on localhost:5432!
pgloader mysql://root:1@localhost/ib_current postgres://intbpro:1@localhost/intbpro
PGPASSWORD=1 pg_dump -h localhost -U intbpro intbpro --inserts -n ib_current --no-owner | sed -e '/^--/d' | sed -e '/^CREATE SCHEMA/d' > $BUILDDIR/db/sql/postgres_new.sql
echo "CREATE INDEX idx_gin_text ON ib_current.ib_text USING gin (to_tsvector( 'russian', \"data\")) WHERE \"type\"=16;" >> $BUILDDIR/db/sql/postgres_new.sql
echo "CREATE INDEX idx_gin_topic ON ib_current.ib_topic USING gin (to_tsvector( 'russian', title || descr)) WHERE "status"='0';" >> $BUILDDIR/db/sql/postgres_new.sql
echo "CREATE FUNCTION ib_current.ib_enum_is_first_from_str (text) returns ib_current.ib_file_is_main AS 'select $1::varchar::ib_current.ib_file_is_main' LANGUAGE sql immutable RETURNS NULL ON NULL INPUT;" >> $BUILDDIR/db/sql/postgres_new.sql
echo "CREATE CAST (text AS ib_current.ib_file_is_main) WITH FUNCTION ib_current.ib_enum_is_first_from_str AS ASSIGNMENT;" >> $BUILDDIR/db/sql/postgres_new.sql
echo "CREATE OR REPLACE FUNCTION ib_current.instr (p_str VARCHAR, p_substr VARCHAR) RETURNS integer AS 'SELECT POSITION(p_substr IN p_str)' LANGUAGE sql IMMUTABLE;" >> $BUILDDIR/db/sql/postgres_new.sql

echo Making installation dump for SQLite... 
~/bin/mysql2sqlite $BUILDDIR/db/sql/mysqli_new.sql >  $BUILDDIR/db/sql/sqlite_new.sql
<<<<<<< HEAD
echo Adding triggers for FTS...
cat db/sql/sqlite_triggers.sql >> $BUILDDIR/db/sql/sqlite_new.sql
=======
>>>>>>> 1a1624e (Initial commit for Intb 3.05)

echo Packing...
rm distr/intbpro.zip
rm distr/intbpro.7z
7z a -r -ssc distr/intbpro.zip $BUILDDIR/* $BUILDDIR/.htaccess > /dev/null
7z a -r -ssc distr/intbpro.7z $BUILDDIR/* $BUILDDIR/.htaccess > /dev/null
