<?php  
            require('/srv/new-pegasus.isi.edu/includes/common.php'); 
            pegasus_header("1.6. Information Catalogs");
        ?><div class="breadcrumbs">
<span class="breadcrumb-link"><a href="index.php">Scientific Workflows through Pegasus WMS at USC HPC Cluster</a></span> &gt; <span class="breadcrumb-link"><a href="tutorial.php">Tutorial</a></span> &gt; <span class="breadcrumb-node">Information Catalogs</span>
</div><hr><div class="section">
<div class="titlepage"><div><div><h2 class="title" style="clear: both">
<a name="tutorial_catalogs"></a>1.6. Information Catalogs</h2></div></div></div>
<div class="toc"><dl class="toc">
<dt><span class="section"><a href="tutorial_catalogs.php#tut_site_catalog">1.6.1. The Site Catalog</a></span></dt>
<dt><span class="section"><a href="tutorial_catalogs.php#idm523385532256">1.6.2. The Transformation Catalog</a></span></dt>
<dt><span class="section"><a href="tutorial_catalogs.php#idm523385525344">1.6.3. The Replica Catalog</a></span></dt>
</dl></div>
<p>The workflow description (DAX) that you specify to Pegasus is
    portable, and usually does not contain any locations to physical input
    files, executables or cluster end points where jobs are executed. Pegasus
    uses three information catalogs during the planning process.</p>
<div class="figure">
<a name="idm523385563200"></a><p class="title"><b>Figure 1.9. Information Catalogs used by Pegasus</b></p>
<div class="figure-contents"><div class="mediaobject"><img src="images/tutorial-pegasus-catalogs.png" alt="Information Catalogs used by Pegasus"></div></div>
</div>
<br class="figure-break"><div class="section">
<div class="titlepage"><div><div><h3 class="title">
<a name="tut_site_catalog"></a>1.6.1. The Site Catalog</h3></div></div></div>
<p>The site catalog describes the sites where the workflow jobs are
      to be executed. In this tutorial we assume that you have a Personal
      Condor pool running on localhost. If you are using one of the tutorial
      VMs this has already been setup for you. The site catalog for the
      tutorial examples is in <code class="filename">sites.xml</code>:</p>
<pre class="programlisting">$ <span class="bold"><strong>more sites.xml</strong></span>
...
   &lt;!-- The local site contains information about the submit host --&gt;
    &lt;site handle="local"&gt;
        <span class="bold"><strong>&lt;!-- This is where intermediate data will be stored --&gt;</strong></span>
        &lt;directory type="shared-scratch" path="/auto/rcf-40/vahi/tutorial/split/scratch"&gt;
            &lt;file-server operation="all" url="file:///auto/rcf-40/vahi/tutorial/split/scratch"/&gt;
        &lt;/directory&gt;
        <span class="bold"><strong>&lt;!-- This is where output data will be stored --&gt;</strong></span>
        &lt;directory type="shared-storage" path="/auto/rcf-40/vahi/tutorial/split/output"&gt;
            &lt;file-server operation="all" url="file:///auto/rcf-40/vahi/tutorial/split/output"/&gt;
        &lt;/directory&gt;
    &lt;/site&gt;

    &lt;site handle="usc-hpcc" arch="x86_64" os="LINUX"&gt;
         <span class="bold"><strong>&lt;!-- Scratch directory on the cluster --&gt;</strong></span>
        &lt;directory type="shared-scratch" path="/auto/rcf-40/vahi/tutorial/split/hpcc/scratch"&gt;
            &lt;file-server operation="all" url="file:///auto/rcf-40/vahi/tutorial/split/hpcc/scratch"/&gt;
        &lt;/directory&gt;


        &lt;profile namespace="pegasus" key="style"&gt;glite&lt;/profile&gt;


        <span class="bold"><strong>&lt;!-- This tells glite what scheduler to use. It can be 'pbs' or 'sge' --&gt;</strong></span>
        &lt;profile namespace="condor" key="grid_resource"&gt;pbs&lt;/profile&gt;

       <span class="bold"><strong> &lt;!-- This tells glite what batch queue to submit jobs to --&gt;</strong></span>
        &lt;profile namespace="pegasus" key="queue"&gt;default&lt;/profile&gt;

       <span class="bold"><strong>&lt;!--- This tells pegasus to have the auxillary jobs run on submit host 
             and not go through the local PBS queue --&gt;</strong></span>
       &lt;profile namespace="pegasus"	key="auxillary.local"&gt;true&lt;/profile&gt;
               
       &lt;!-- This profile tells Pegasus where the worker package is installed on the site --&gt;
        &lt;!-- Without this, Pegasus will automatically stage a worker package to the site,
             which fails because USC worker nodes are behind a firewall --&gt;
        &lt;profile namespace="env" key="PEGASUS_HOME"&gt;/home/rcf-proj/gmj/pegasus/SOFTWARE/pegasus/default&lt;/profile&gt; 



...
      </pre>
<div class="note" style="margin-left: 0.5in; margin-right: 0.5in;">
<h3 class="title">Note</h3>
<p>By default (unless specified in properties), Pegasus picks ups
        the site catalog from a XML file named sites.xml in the current
        working directory from where pegasus-plan is invoked.</p>
</div>
<p>There are two sites defined in the site catalog: "local" and
      "condorpool". The "local" site is used by Pegasus to learn about the
      submit host where the workflow management system runs. The "condorpool"
      site is the Condor pool configured on your submit machine. In the case
      of the tutorial VM, the local site and the condorpool site refer to the
      same machine, but they are logically separate as far as Pegasus is
      concerned.</p>
<div class="orderedlist"><ol class="orderedlist" type="1">
<li class="listitem"><p>The <span class="bold"><strong>local</strong></span> site is configured
          with a "storage" file system that is mounted on the submit host
          (indicated by the file:// URL). This file system is where the output
          data from the workflow will be stored. When the workflow is planned
          we will tell Pegasus that the output site is "local".</p></li>
<li class="listitem"><p>The <span class="bold"><strong>condorpool</strong></span> site is also
          configured with a "scratch" file system. This file system is where
          the working directory will be created. When we plan the workflow we
          will tell Pegasus that the execution site is "condorpool".</p></li>
</ol></div>
<p>Pegasus supports many different file transfer protocols. In this
      case the Pegasus configuration is set up so that input and output files
      are transferred to/from the condorpool site by Condor. This is done by
      setting <code class="literal">pegasus.data.configuration = condorio</code> in the
      properties file. In a normal Condor pool, this will cause job
      input/output files to be transferred from/to the submit host to/from the
      worker node. In the case of the tutorial VM, this configuration is just
      a fancy way to copy files from the workflow scratch directory to the job
      scratch directory.</p>
<p>Finally, the condorpool site is configured with two profiles that
      tell Pegasus that it is a plain Condor pool. Pegasus supports many ways
      of submitting tasks to a remote cluster. In this configuration it will
      submit vanilla Condor jobs.</p>
<div class="section">
<div class="titlepage"><div><div><h4 class="title">
<a name="idm523385542640"></a>1.6.1.1. HPC Clusters</h4></div></div></div>
<p>Typically the sites in the site catalog describe remote
        clusters, such as PBS clusters or Condor pools.</p>
<p>Usually, a typical deployment of an HPC cluster is illustrated
        below. The site catalog, captures for each cluster (site)</p>
<div class="itemizedlist"><ul class="itemizedlist" style="list-style-type: disc; ">
<li class="listitem"><p>directories that can be used for executing jobs</p></li>
<li class="listitem"><p>whether a shared file system is available</p></li>
<li class="listitem"><p>file servers to use for staging input data and staging out
            output data</p></li>
<li class="listitem"><p>headnode of the cluster to which jobs can be
            submitted.</p></li>
</ul></div>
<div class="figure">
<a name="idm523385536944"></a><p class="title"><b>Figure 1.10. Sample HPC Cluster Setup</b></p>
<div class="figure-contents"><div class="mediaobject"><img src="images/tutorial-hpc-cluster.png" alt="Sample HPC Cluster Setup"></div></div>
</div>
<br class="figure-break"><p>Below is a sample site catalog entry for HPC cluster at SDSC
        that is part of XSEDE</p>
<pre class="programlisting">&lt;site  handle="sdsc-gordon" arch="x86_64" os="LINUX"&gt;
        &lt;grid  type="gt5" contact="gordon-ln4.sdsc.xsede.org:2119/jobmanager-fork" scheduler="Fork" jobtype="auxillary"/&gt;
        &lt;grid  type="gt5" contact="gordon-ln4.sdsc.xsede.org:2119/jobmanager-pbs" scheduler="unknown" jobtype="compute"/&gt;

        &lt;!-- the base directory where workflow jobs will execute for the site --&gt;
        &lt;directory type="shared-scratch" path="/oasis/scratch/ux454281/temp_project"&gt;
            &lt;file-server operation="all" url="gsiftp://oasis-dm.sdsc.xsede.org:2811/oasis/scratch/ux454281/temp_project"/&gt;
        &lt;/directory&gt;

        &lt;profile namespace="globus" key="project"&gt;TG-STA110014S&lt;/profile&gt;
        &lt;profile namespace="env" key="PEGASUS_HOME"&gt;/home/ux454281/software/pegasus/pegasus-4.5.0&lt;/profile&gt;
    &lt;/site&gt;</pre>
</div>
</div>
<div class="section">
<div class="titlepage"><div><div><h3 class="title">
<a name="idm523385532256"></a>1.6.2. The Transformation Catalog</h3></div></div></div>
<p>The transformation catalog describes all of the executables
      (called "transformations") used by the workflow. This description
      includes the site(s) where they are located, the architecture and
      operating system they are compiled for, and any other information
      required to properly transfer them to the execution site and run
      them.</p>
<p>For this tutorial, the transformation catalog is in the file
      <code class="filename">tc.txt</code>:</p>
<pre class="programlisting">$ <span class="bold"><strong>more tc.txt</strong></span>
tr wc {
    site condorpool {
        pfn "/usr/bin/wc"
        arch "x86_64"
        os "linux"
        type "INSTALLED"
    }
}
...</pre>
<div class="note" style="margin-left: 0.5in; margin-right: 0.5in;">
<h3 class="title">Note</h3>
<p>By default (unless specified in properties), Pegasus picks up
        the transformation catalog from a text file named tc.txt in the
        current working directory from where pegasus-plan is invoked.</p>
</div>
<p>The <code class="filename">tc.txt</code> file contains information about
      two transformations: wc, and split. These three transformations are
      referenced in the split DAX. The transformation catalog indicates that
      both transformations are installed on the condorpool site, and are
      compiled for x86_64 Linux.</p>
</div>
<div class="section">
<div class="titlepage"><div><div><h3 class="title">
<a name="idm523385525344"></a>1.6.3. The Replica Catalog</h3></div></div></div>
<p><span class="bold"><strong>Note:</strong></span> Replica Catalog
      configuration is not required for the tutorial setup. It is only
      required if you want to refer to input files on external servers.</p>
<p>The example that you ran, was configured with the inputs already
      present on the submit host (where Pegasus is installed) in a directory.
      If you have inputs at external servers, then you can specify the URLs to
      the input files in the Replica Catalog. This catalog tells Pegasus where
      to find each of the input files for the workflow.</p>
<p>All files in a Pegasus workflow are referred to in the DAX using
      their Logical File Name (LFN). These LFNs are mapped to Physical File
      Names (PFNs) when Pegasus plans the workflow. This level of indirection
      enables Pegasus to map abstract DAXes to different execution sites and
      plan out the required file transfers automatically.</p>
<p>The Replica Catalog for the diamond workflow is in the
      <code class="filename">rc.txt</code> file:</p>
<pre class="programlisting">$ <span class="bold"><strong>more rc.txt</strong></span>
# This is the replica catalog. It lists information about each of the
# input files used by the workflow. You can use this to specify locations to input files present on external servers.

# The format is:
# LFN     PFN    pool="SITE"
#
# For example:
#data.txt  file:///tmp/data.txt         site="local"
#data.txt  http://example.org/data.txt  site="example"
pegasus.html file:///home/tutorial/split/input/pegasus.html   site="local"
</pre>
<div class="note" style="margin-left: 0.5in; margin-right: 0.5in;">
<h3 class="title">Note</h3>
<p>By default (unless specified in properties), Pegasus picks ups
        the transformation catalog from a text file named tc.txt in the
        current working directory from where pegasus-plan is invoked. In our
        tutorial, input files are on the submit host and we used the --input
        dir option to pegasus-plan to specify where they are located.</p>
</div>
<p>This replica catalog contains only one entry for the split
      workflow’s only input file. This entry has an LFN of "pegasus.html" with
      a PFN of "file:///home/tutorial/split/input/pegasus.html" and the file
      is stored on the local site, which implies that it will need to be
      transferred to the condorpool site when the workflow runs.</p>
</div>
</div><div class="navfooter">
<hr>
<table width="100%" summary="Navigation footer">
<tr>
<td width="40%" align="left">
<a accesskey="p" href="tutorial_wf_generation.php">Prev</a> </td>
<td width="20%" align="center"><a accesskey="u" href="tutorial.php">Up</a></td>
<td width="40%" align="right"> <a accesskey="n" href="tutorial_configuration.php">Next</a>
</td>
</tr>
<tr>
<td width="40%" align="left" valign="top">1.5. Generating the Workflow </td>
<td width="20%" align="center"><a accesskey="h" href="index.php">Table of Contents</a></td>
<td width="40%" align="right" valign="top"> 1.7. Configuring Pegasus</td>
</tr>
</table>
</div><?php  
            pegasus_footer();
        ?>
