#!/usr/bin/perl

use strict;
use DBI;
use Bio::SeqIO;
use Bio::Seq;
use Bio::SeqFeature::Generic;

my $file = "../../conf.php";
my $db;
my $host;
my $user;
my $pass;
my $item;
my @geneCodes;
my $geneCode;
my $sequence;

open( INFILE, $file) or die "Couldnt read $file: $!";

foreach my $line (<INFILE>) {
	if( $line =~ /(^\$db.*)/ ) {
		if( $line =~ /^\$\w+\s+=\s+'(\w+)'/ ) {
			$db = $1;
		}
	}
	if( $line =~ /(^\$host.*)/ ) {
		if( $line =~ /^\$\w+\s+=\s+'(\w+)'/ ) {
			$host = $1;
		}
	}
	if( $line =~ /(^\$pass.*)/ ) {
		if( $line =~ /^\$\w+\s+=\s+'(\w+)'/ ) {
			$pass = $1;
		}
	}
	if( $line =~ /(^\$user.*)/ ) {
		if( $line =~ /^\$\w+\s+=\s+'(\w+)'/ ) {
			$user = $1;
		}
	}
}
close(INFILE);

my $dbh = DBI->connect("dbi:mysql:database=$db;mysql_socket=/tmp/mysql.sock", $user, $pass) or die "Cannot connect to database: $DBI::errstr";
my $query = "select distinct geneCode from sequences";
my $sth = $dbh->prepare($query);
$sth->execute();

while(my @data = $sth->fetchrow_array()) {
	foreach $item (@data) {
		push @geneCodes, $item;
	}
}

my $out = Bio::SeqIO->new(-file => ">$db.fas", '-format' => "fasta");
# get an array with sequences, geneCode and code and produce files with them, one per geneCode
foreach $geneCode (@geneCodes) {
	my $feat = new Bio::SeqFeature::Generic(
			-tag     => { exon => "$geneCode" } );
	$feat->add_tag_value("author", "carlos pena");

	$query = "SELECT code, sequences FROM sequences WHERE geneCode =\"$geneCode\" order by code";
	$sth = $dbh->prepare($query);
	$sth->execute();

	$geneCode = lc($geneCode);

	while(my @data = $sth->fetchrow_array()) {
		$data[1] = uc($data[1]);
		$data[1] = trim($data[1]);
#$data[1] = "---????ABCABCABCAA????--?????ABCABCABB??-?????ACC?-????DD???----?????AAAAAAAAAAAAAAAAAAAAAA"; ####
		$data[1] =~ s/-/\?/g;
		if($data[1] =~ /(^\w{1,45}\?+|^\?+)(.+)/ ) {
			$sequence = $2;
			my $tmp = $1;
			my $i = 0;
			while($i < 5) {
				undef $tmp;
				$sequence =~ /(^\w{1,45}\?+|^\?+)(.+)/; 
				$sequence = $2;
				$i ++;
			}
		}
		else {
			$sequence = $data[1];
		}
		
		$sequence = trim($sequence);
		if($sequence ne "") {
#print $geneCode . "\t" . $data[0] . "\t" . $sequence . "\n";
			$data[0] = $data[0] . "___" . $geneCode;
			my $seq = Bio::Seq->new( -seq => $sequence,
								-id => $data[0],
								-accession_number => $data[0],
								);
			$seq->add_SeqFeature($feat);
			$out->write_seq($seq);
		}
	}
}

sub trim($)
{
	my $string = shift;
	$string =~ s/^\s+//;
	$string =~ s/\s+$//;
	return $string;
}
