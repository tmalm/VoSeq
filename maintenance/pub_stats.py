#!/usr/bin/python

# This script queries the publication table and produces several statistics

import MySQLdb;
from utils import parse_conf;

#parse conf.php file
conf = open("../conf.php");
credentials = parse_conf(conf);
conf.close()
host	= credentials['host']
user	= credentials['user']
passwd	= credentials['passwd']
db		= credentials['db']

mysql = MySQLdb.connect(unix_socket="/tmp/mysql.sock", host=host, user=user, passwd=passwd, db=db);
cursor = mysql.cursor()

# Number of accessions in table publications:
cursor.execute("SELECT distinct accession FROM publications WHERE accession is not null and accession !='' and accession != 'null'");
result = cursor.fetchall()
accessions_pub = [];
for item in result:
	accessions_pub.append(item[0]);

# Number of accessions in table sequences:
cursor.execute("SELECT distinct accession FROM sequences WHERE accession is not null and accession !='' and accession != 'null'");
result = cursor.fetchall()
accessions_seq = [];
for item in result:
	accessions_seq.append(item[0]);

if accessions_seq > accessions_pub:
	number_of_accessions = accessions_seq;
elif accessions_seq < accessions_pub:
	number_of_accessions = accessions_pub;
else:
	number_of_accessions = accessions_seq;


# Number of doi in table sequences:
cursor.execute("SELECT distinct doi FROM publications WHERE doi is not null and doi !='' and doi != 'null'");
result = cursor.fetchall()
dois = [];
for item in result:
	dois.append(item[0]);

# Number of pubmed in table sequences:
cursor.execute("SELECT distinct pubmed FROM publications WHERE pubmed is not null and pubmed !='' and pubmed != 'null'");
result = cursor.fetchall()
pubmeds = [];
for item in result:
	pubmeds.append(item[0]);

if len(dois) > len(pubmeds):
	number_of_publications = dois;
elif len(dois) < len(pubmeds):
	number_of_publications = pubmeds;
else:
	number_of_publications = dois;

print "\nNumber of sequences published with accession numbers:\t", len(number_of_accessions);
print "Number of articles published:\t", len(number_of_publications);
