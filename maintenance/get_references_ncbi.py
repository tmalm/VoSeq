#!/usr/bin/python

# This script will update the table publications.
# it will take the accession numbers from table sequences and will query the pubmed and citations from Entrez

#tabla publications
# accession1 -> pubmed, citation, doi, unpublished?
# accession1 -> pubmed2, citation2, doi, unpublished?

from Bio import Entrez;
import string;
import re;
import MySQLdb;
import sys
import time;

from utils import fetch_pubmed;
from utils import fetch_citation;
from utils import parse_conf;
from utils import find_acc_pubmed;
from utils import check_requests;

Entrez.email = "mycalesis@gmail.com"

# Log file
f = open("references.log", "w");

# parse conf.php file
conf = open("../conf.php");
credentials = parse_conf(conf);
conf.close()
host	= credentials['host']
user	= credentials['user']
passwd	= credentials['passwd']
db		= credentials['db']

mysql = MySQLdb.connect(unix_socket="/tmp/mysql.sock", host=host, user=user, passwd=passwd, db=db);
cursor = mysql.cursor()
cursor.execute("SELECT accession FROM sequences WHERE accession is not null \
						AND accession != '' AND accession != 'null' ORDER BY accession");
result = cursor.fetchall()

accessions = [];
for item in result:
	accessions.append(item[0])

# get current pairs accession -> pubmed in table publications
pairs = find_acc_pubmed();

# for each accession look up PubMed id
i = 0;
#accessions = ["GQ357638"]; # for testing

for accession in accessions:
	print accession
	f.write("testing: " + accession + "\n")
	if pairs:
		# if acc : non-empty in pairs continue, else
		try:
			pairs[accession] != ""
			print "done\n"
			f.write("done\n\n")
			continue;
		except:
			i = check_requests(i);
			pubmed = fetch_pubmed(accession)
			i += 1;

			if pubmed and pubmed[0] == "pubmed":
				# if pair accession -> PubMed is not in table publications download citation and upload to db
				i = check_requests(i);
				citation = fetch_citation(pubmed)
				i += 1;

				query = "insert into publications (accession, pubmed, citation, doi, unpublished, timestamp) values"
				query += "('" + accession + "', '" + pubmed[1] + "', '" + citation[0] + "', '" + citation[1] + "', '" + "0', now())"
				print query ,"\n"
				f.write(query + "\n\n")
				cursor.execute(query)
				continue;
			elif pubmed and pubmed[0] == "ref":
				print "PubMed not found. Scraping Genbank record"
				f.write("PubMed not found. Scraping Genbank record" + "\n")
				re.escape(pubmed[1])
				query = "insert into publications (accession, pubmed, citation, unpublished, timestamp) values"
				query += "('" + accession + "', null, '" + pubmed[1] + "', '" + "1', now())"
				cursor.execute(query)
				print query ,"\n"
				f.write(query + "\n\n")
			else:
				# fetch_pubmed couldn't get any from NCBI
				print "The script couldn't get any record from NCBI\n"
				f.write("The script couldn't get any record from NCBI\n\n")
	else:
		# if pair accession -> PubMed is not in table publications download citation and upload to db
		i = check_requests(i);
		pubmed = fetch_pubmed(accession)
		i += 1;

		if pubmed[0] == "pubmed":
			i = check_requests(i);
			citation = fetch_citation(pubmed)
			i += 1;

			query = "insert into publications (accession, pubmed, citation, doi, unpublished, timestamp) values"
			query += "('" + accession + "', '" + pubmed[1] + "', '" + citation[0] + "', '" + citation[1] + "', '" + "0', now())"
			print query
			f.write(query + "\n\n")
			cursor.execute(query)

f.close()
