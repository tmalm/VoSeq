<?php
// #################################################################################
// #################################################################################
// Voseq share_data_gbif.php
// author(s): Carlos Peña & Tobias Malm
// license   GNU GPL v2
// source code available at https://github.com/carlosp420/VoSeq
//
// Script overview: Displays information how to share your data with GBIF
// #################################################################################
// #################################################################################
// Section: Startup/includes
// #################################################################################
//check login session
include'login/auth.php';

error_reporting (E_ALL ^ E_NOTICE);

// includes
ob_start();//Hook output buffer - disallows web printing of file info...
include'conf.php';
ob_end_clean();//Clear output buffer//includes
include 'functions.php';
include 'markup-functions.php';
include 'includes/validate_coords.php';

// need dojo?
$dojo = false;

// which dojo?
#$whichDojo[] = 'Tooltip';
#$whichDojo[] = 'ComboBox';

// to indicate this is an not an administrator page
$admin = false;

// #################################################################################
// Section: Output
// #################################################################################
// get title
$title = "$config_sitename - Share data with GBIF";
				
// print html headers
include_once('includes/header.php');
nav();
				
// begin HTML page content
echo "<div id=\"content\">";
	
?>
	
<!-- 	show previous and next links -->
<table border="0" width="960px"> <!-- super table -->
	<tr>
		<td valign="top">
			<h1>Integration with GBIF</h1>

			<p>You can share your information hosted in VoSeq with GBIF.</p>

			<p><b>GBIF</b> prefers data owners to use their <a href="http://www.gbif.org/informatics/infrastructure/publishing/#c889">Integrated Publishing Toolkit (IPT)</a>. This means that you can install their IPT software to produce a resource in Darwin Core format that can be harvested by GBIF. In addition to the actual data in your VoSeq installation, <b>IPT allows you to include a rich variety of metadata</b> for GBIF.</p>

			<p>VoSeq is able to produce a <b>dump file</b> containing all the data you own. Then you can import this file into a IPT installation and choose which types of data you want to publish via GBIF. Once you include all the metadata required by GBIF you have two choices in order to expose your data taken from <a href="http://www.gbif.org/informatics/standards-and-tools/publishing-data/">GBIF website:</a></p>

    <ul>
		<li>By setting up a dynamic server software:</li>
			<ul>
				<li>Acquire hardware with a permanent Internet connection (a regular PC is sufficient).</li>
				<li>Install data publishing software. GBIF recommends the Integrated Publishing Toolkit (IPT). You will need a web server such as Apache.</li>
				<li>Configure the software for your local data structure; this is the "mapping" process. Please follow the documentation of your chosen publishing software for this process.</li>
				<li>Register your service with GBIF and sign the GBIF Data Sharing Agreement.</li>
			</ul>
		<li>Create an archive for your entire dataset:</li>
			<ul>
				<li>This scenario doesn't require a permanent Internet connection.</li>
				<li>You simply need to create a Darwin Core Archive, upload it to a repository (for example an IPT repository installed by your GBIF Participant Node, an institutional FTP or web server, or a service like Dropbox or the Internet Archive).</li>
				<li>You then just need to register the public URL for the storage location of your archive with GBIF.</li>
			</ul>
	</ul>

		<script type="text/javascript">

			function dump_data() {
				$.post('dump_data.php', { request: "count_data" },
					function(data) {
						var out = '';
						
						out =  "<br /><br /><p>A total of <b>" + data.count + "</b> records were processed for GBIF. ";
						out += "<br />A MS EXCEL will be created with all data for GBIF. <a href='dump_data.php?request=make_file'>Click here to Download</a>. ";
						out += "<p>Submit to GBIF by using an installation of their ";
						out += "<a href='http://www.gbif.org/informatics/infrastructure/publishing/#c889'>Integrated Publishing Toolkit (IPT)</a>.</p>";
						out += "<p>See <a href='http://nymphalidae.utu.fi/cpena/VoSeq_docu.html'>VoSeq documentation</a> on how to upload this dump file into IPT.</p>";

						$('#output').empty().html('<img src="images/loading.gif" />').fadeIn('slow', function(){}).delay(1000).fadeOut('slow', function() { 
																																	$('#output').html(out).show();
																																	});

					}, "json");

				// make and download MS EXCEL file
				$.get('dump_data.php', { request: "make_file" });
			}

		</script>
	
		<button onClick="dump_data();">Dump data for GBIF</button>
		<div id="output"></div>

</td>
<td>
	<?php make_sidebar();  ?>
</td>
</tr>
</table> <!-- end super table -->


</div> <!-- end content -->

<?php

make_footer($date_timezone, $config_sitename, $version, $base_url);

?>

	
</body>
</html>
