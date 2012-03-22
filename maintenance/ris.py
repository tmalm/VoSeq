#!/usr/bin/python

import re;
import string;

class RISparser():
	def checkFormat(self, source):
		"""
		is this RIS format?
		"""
		pattern = re.compile('^[0-9|A-Z]{2}  - ', re.MULTILINE);
		all_tags = re.findall(pattern, source);
		if len(all_tags) and "AU  - " in all_tags:
			return 1;
		return 0;

	def parse(self, source):
		pattern = re.compile('^AU  - (.+)', re.M)
		authors = re.findall(pattern, source);
		n_authors = "";
		for i in range(len(authors)):
			if i == len(authors) - 2:
				n_authors += authors[i] + " & " 
			elif i == len(authors) -1:
				n_authors += authors[i] + ". "
			else:
				n_authors += authors[i] + ", "
		authors = re.escape(n_authors)

		pattern = re.compile('^DP  - ([0-9]{4}).*', re.M)
		date_published = re.findall(pattern, source); 
		date_published = date_published[0]

		pattern = re.compile('^TI  - (.+)\n\s+(\s.+)\s+(\s.+)', re.M)
		title = re.findall(pattern, source); 
		for item in title:
			title = item[0]
			if item[1]:
				title += item[1]
			if item[2]:
				title += item[2]
		title = string.replace(title, "\'", "\\'")

		pattern = re.compile('^JT  - (.+)', re.M)
		journal = re.findall(pattern, source); 
		journal = journal[0];
		journal = string.capwords(journal)
		journal = string.replace(journal, "'", "\'")

		pattern = re.compile('^VI  - ([0-9]+)', re.M)
		volume = re.findall(pattern, source); 
		volume = volume[0];

		pattern = re.compile('^IP  - ([0-9]+)', re.M)
		issue = re.findall(pattern, source); 
		if issue:
			issue = issue[0];
		else:
			issue = "";

		pattern = re.compile('^PG  - (.+)', re.M)
		pages = re.findall(pattern, source); 
		pages = pages[0];

		pattern = re.compile('^AID - (.+)\s{1}\[doi\]', re.M)
		doi = re.findall(pattern, source); 
		if doi:
			doi = doi[0];
		else:
			doi = ""

		# make a dictionary
		citation = {'authors'        : authors,
					'date_published' : date_published,
					'title'          : title,
					'journal'        : journal,
					'volume'         : volume,
					'issue'          : issue,
					'pages'          : pages,
					'doi'            : doi
					}
		return citation;

