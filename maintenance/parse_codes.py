#!/usr/bin/python

import re;
import string;

f = open("a.csv", "r");
lines = f.readlines();

pattern = "([A-Z]{2}[0-9]{2,3}-[0-9]{2})";

for line in lines:
	m = re.findall(pattern, line);
	if len(m) > 0:
		print m


f.close();
