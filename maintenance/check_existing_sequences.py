#!/usr/bin/python

import MySQLdb as mdb
import sys
import string
import re

if len(sys.argv) < 2:
	print "Enter your file of Code,GeneCode to check wheter those sequences exist or not in the database";
	sys.exit()

credentials = []
##### get username, pass and db from conf.php
for line in open("../conf.php", "r"):
	host = re.search("^\$host\s*=\s*'\w+'", line);
	if host != None:
		host = host.group();
		host = host.split("=");
		host = string.replace(host[1], "'", "").strip()
		credentials.append(host)

	user = re.search("^\$user\s*=\s*'\w+'", line);
	if user != None:
		user = user.group();
		user = user.split("=");
		user = string.replace(user[1], "'", "").strip()
		credentials.append(user)

	passwd = re.search("^\$pass\s*=\s*'\w+'", line);
	if passwd != None:
		passwd = passwd.group();
		passwd = passwd.split("=");
		passwd = string.replace(passwd[1], "'", "").strip()
		credentials.append(passwd)

	db = re.search("^\$db\s*=\s*'\w+'", line);
	if db != None:
		db = db.group();
		db = db.split("=");
		db = string.replace(db[1], "'", "").strip()
		credentials.append(db)


#host, user, passwd, db
#credentials

file_out1 = open("seqs_exist_in_db.txt", "w");
file_out2 = open("seqs_dont_exist_in_db.txt", "w");
file_out3 = open("vouchers_dont_exist_in_db.txt", "w");
file_out4 = open("vouchers_exist_in_db.txt", "w");

con = None
try:
	con = mdb.connect(unix_socket="/tmp/mysql.sock", host=credentials[0], user=credentials[1], passwd=credentials[2], db=credentials[3]);
	cur = con.cursor()

	filename = sys.argv[1];
	for item in open(filename, "r"):
		item = item.split(",");
		cur.execute("select code, geneCode from sequences where code='" + item[0].strip() + "' and geneCode='" + item[1].strip() + "'")
		data = cur.fetchone()
		if data != None:
			print data
			file_out1.write("It IS in database: " + data[0] + ", " + data[1] + "\n");
		else:
			file_out2.write("\nNot in db: " + item[0] + ", " + item[1]);

		# check for vouchers
		cur.execute("select code from vouchers where code='" + item[0].strip() + "'")
		data = cur.fetchone()
		if data == None:
			print "Voucher not in database" + item[0];
			file_out3.write("\nNot in db: " + item[0] );
		else:
			file_out4.write("It is in db: " + item[0] );

except mdb.Error, e:
	print "Error %d: %s" % (e.args[0], e.args[1])
	sys.exit(1)
