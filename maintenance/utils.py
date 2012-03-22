import MySQLdb;
from Bio import Entrez;
from ris import RISparser;

import re;
import string;
import time;

# Function to fetch PubMed Id from NCBI based on accession number
def fetch_pubmed(accession):
	handle = Entrez.efetch(db="nuccore", id=accession, rettype="gb")

	result = []
	authors = "";
	title   = "";
	journal = "";
	for line in handle:
		pubmed = "";
		pattern = re.compile('^\s+PUBMED\s+(.+)', re.M)
		if re.findall(pattern, line):
			pubmed = re.findall(pattern, line)

			result.append("pubmed")
			result.append(pubmed[0])
			return result
		else:
			pattern = re.compile('^\s+AUTHORS\s+(.+)', re.M)
			if re.findall(pattern, line):
				authors = re.findall(pattern, line)

			pattern = re.compile('^\s+TITLE\s+(.+)', re.M)
			if re.findall(pattern, line):
				title = re.findall(pattern, line)

			pattern = re.compile('^\s+JOURNAL\s+(.+)', re.M)
			if re.findall(pattern, line):
				journal = re.findall(pattern, line)

	if authors:
		authors = authors[0]

	if title:
		title = title[0]

	if journal:
		journal = journal[0]

	if authors != "" or title != "" or journal != "":
		ref = authors + " " + title + " " + journal
		result.append("ref")
		result.append(ref)
		return result

#except:
#print "# NCBI don't have record or has not released data of this accession number"
#return "None";



# Function to fetch Citation from NCBI based on PubMed id
def fetch_citation(pubmed):
	handle = Entrez.efetch(db="pubmed", id=pubmed, rettype="medline", retmode="text")
	record = handle.read();

	ris = RISparser();
	is_ris = ris.checkFormat(record);

	ref = "";
	result = [];
	if is_ris:
		citation = ris.parse(record);
		ref = citation['authors'] + citation['date_published'] + ". " + citation['title'] \
				+ " " + citation['journal'] + ". " + citation['volume'];
		if citation['issue'] != "":
			ref += "(" + citation['issue'] + "): " + citation['pages'] + " " + citation['doi']
		else:
			ref += ": " + citation['pages'] + " " + citation['doi']
	else:
		 return 0;
			
	result.append(string.strip(ref));
	result.append(citation['doi']);
	return result

# Function to parse conf.php file and return host, user, pass, db
def parse_conf(file):
	for line in file:
		pattern = re.compile('^\$host = \'(.+)\'', re.M)
		if re.findall(pattern, line):
			host = re.findall(pattern, line)
			host = host[0]

		pattern = re.compile('^\$user = \'(.+)\'', re.M)
		if re.findall(pattern, line):
			user = re.findall(pattern, line)
			user = user[0]

		pattern = re.compile('^\$pass = \'(.+)\'', re.M)
		if re.findall(pattern, line):
			passwd = re.findall(pattern, line)
			passwd = passwd[0]

		pattern = re.compile('^\$db\s+= \'(.+)\'', re.M)
		if re.findall(pattern, line):
			db = re.findall(pattern, line)
			db = db[0]

	# make dictionary
	conf = {	'host' : host,
				'user' : user,
				'passwd' : passwd,
				'db'	: db
			}

	return conf

# Function to get current pairs accession -> pubmed in table publications
def find_acc_pubmed():
	conf = open("../conf.php");
	credentials = parse_conf(conf);
	conf.close()
	host = credentials['host']
	user = credentials['user']
	passwd = credentials['passwd']
	db = credentials['db']

	mysql = MySQLdb.connect(unix_socket="/tmp/mysql.sock", host=host, user=user, passwd=passwd, db=db);
	cursor = mysql.cursor()
	cursor.execute("SELECT accession, pubmed FROM publications");
	result = cursor.fetchall()
	pairs = {};
	for item in result:
		i = item[0];
		j = item[1];
		pairs[i] = j

	if len(pairs) < 1:
		return 0;

	return pairs;

def check_requests(i):
	if i == 3:
		time.sleep(1)
		i = 0;
		return i;
	else:
		return i;

