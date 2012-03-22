#!/usr/bin/perl

# USE IT TO UPDATE ARBITRARY COLUMNS WITHOUT AFFECTING THE DATA, JUST BY APPENDING IT
# need a file called 'list.csv' that should contain the info:
# The comma to divide the publication "codes" will be included by this script
# 
# "publishedIn", "code"
# "my_string"  , "my_code"
#
# carlos pena 2007-04-26
#
# TODO: have to check for records that might not be already in the database

use DBI;
use strict;

my $db = 'filemaker';
my $host = 'localhost';
my $user = 'root';
my $pass = 'mysqlboriska';
my $filename;
my $line;
my $publishedIn;
my @row;

if (!@ARGV) {
	print "You need to enter the file name of the .csv file\n";
	print "'publishedIn', 'code'\n";
	print "'my_string', 'my_code'\n";
}
else {
	$filename = @ARGV[0];
	open(INFILE, $filename) or die "Can't open input.txt: $!";
	open(OUTFILE, ">todb");

	my $dbh = DBI->connect("dbi:mysql:database=$db;mysql_socket=/tmp/mysql.sock", $user, $pass) or die "Cannot connect to database: $DBI::errstr";

	my $query = "set names utf8";
	my $sth = $dbh->prepare($query);
	$sth->execute();

	# start writing file to upload to MySQL
	print OUTFILE "\nset names utf8;\n";
	foreach $line (<INFILE>) {
		# clean quotes
		$line =~ s/"//g;
		my @line = split /,/,$line;
		chomp $line[1];
		$line[1] =~ s/^\s+//;
		my $code = $line[1];
	
		my $query = "SELECT publishedIn, code FROM vouchers WHERE code='$code'";
		my $sth = $dbh->prepare($query);
		my $rv = $sth->execute();

		if($rv eq "0E0") {
			print $code, " missing!!!!\n";
		}
		else {
			while( @row = $sth->fetchrow_array ) {
				my $old_publishedIn = $row[0];
				chomp $old_publishedIn;
				$old_publishedIn =~ s/^\s+//g;
				$old_publishedIn =~ s/\s+$//g;
		
				if( $old_publishedIn eq "" ) {
					$publishedIn = $line[0];
				}
				else {
					$publishedIn = $old_publishedIn . ", " . $line[0];
				}
				my $string = "update vouchers set publishedIn='$publishedIn' where code='$code';";
				print OUTFILE $string, "\n";
			}
		}
	}
	close(OUTFILE);
	close(INFILE);
	print "\n\n\tThe file 'todb' is ready to be uploaded to the MySQL database:\n\t\tdo: mysql -u root -p < todb\n\n";
}
