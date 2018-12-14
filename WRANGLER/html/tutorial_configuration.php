<?php  
            require('/srv/new-pegasus.isi.edu/includes/common.php'); 
            pegasus_header("1.7. Configuring Pegasus");
        ?><div class="breadcrumbs">
<span class="breadcrumb-link"><a href="index.php">Scientific Workflows through Pegasus WMS at USC HPC Cluster</a></span> &gt; <span class="breadcrumb-link"><a href="tutorial.php">Tutorial</a></span> &gt; <span class="breadcrumb-node">Configuring Pegasus</span>
</div><hr><div class="section">
<div class="titlepage"><div><div><h2 class="title" style="clear: both">
<a name="tutorial_configuration"></a>1.7. Configuring Pegasus</h2></div></div></div>
<p>In addition to the information catalogs, Pegasus takes a
    configuration file that specifies settings that control how it plans the
    workflow.</p>
<p>For the diamond workflow, the Pegasus configuration file is
    relatively simple. It only contains settings to help Pegasus find the
    information catalogs. These settings are in the
    <code class="filename">pegasus.properties</code> file:</p>
<pre class="programlisting">$ <span class="bold"><strong>more pegasus.properties</strong></span>
# This tells Pegasus where to find the Site Catalog
pegasus.catalog.site.file=sites.xml

# This tells Pegasus where to find the Replica Catalog
pegasus.catalog.replica=File
pegasus.catalog.replica.file=rc.txt

# This tells Pegasus where to find the Transformation Catalog
pegasus.catalog.transformation=Text
pegasus.catalog.transformation.file=tc.txt

# Use condor to transfer workflow data
pegasus.data.configuration=condorio

# This is the name of the application for analytics
pegasus.metrics.app=pegasus-tutorial
</pre>
</div><div class="navfooter">
<hr>
<table width="100%" summary="Navigation footer">
<tr>
<td width="40%" align="left">
<a accesskey="p" href="tutorial_catalogs.php">Prev</a> </td>
<td width="20%" align="center"><a accesskey="u" href="tutorial.php">Up</a></td>
<td width="40%" align="right"> <a accesskey="n" href="tutorial_wf_dashboard.php">Next</a>
</td>
</tr>
<tr>
<td width="40%" align="left" valign="top">1.6. Information Catalogs </td>
<td width="20%" align="center"><a accesskey="h" href="index.php">Table of Contents</a></td>
<td width="40%" align="right" valign="top"> 1.8. Workflow Dashboard for Monitoring and Debugging</td>
</tr>
</table>
</div><?php  
            pegasus_footer();
        ?>
