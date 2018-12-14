<?php  
            require('/srv/new-pegasus.isi.edu/includes/common.php'); 
            pegasus_header("1.8. Workflow Dashboard for Monitoring and Debugging");
        ?><div class="breadcrumbs">
<span class="breadcrumb-link"><a href="index.php">Scientific Workflows through Pegasus WMS at USC HPC Cluster</a></span> &gt; <span class="breadcrumb-link"><a href="tutorial.php">Tutorial</a></span> &gt; <span class="breadcrumb-node">Workflow Dashboard for Monitoring and Debugging</span>
</div><hr><div class="section">
<div class="titlepage"><div><div><h2 class="title" style="clear: both">
<a name="tutorial_wf_dashboard"></a>1.8. Workflow Dashboard for Monitoring and Debugging</h2></div></div></div>
<p>The Pegasus Dashboard is a web interface for monitoring and
    debugging workflows. We will use the web dashboard to monitor the status
    of the split workflow.</p>
<p>By default, the dashboard server can only monitor workflows run by
    the current user i.e. the user who is running the pegasus-service. On
    hpc-pegasus.usc.edu, it is running in a multiuser mode. Before accessing
    the dashboard, you need to give world read permissions to the workflow
    database in your home directory.</p>
<pre class="programlisting">$ <span class="bold"><strong>chmod +r ~/.pegasus/workflow.db</strong></span></pre>
<p>Access the dashboard by navigating your browser to <span class="bold"><strong>https://hpc-pegasus.usc.edu/u/trainXX </strong></span>.</p>
<p>When the webpage loads up, it will ask you for a username and a
    password. It is your USC RCF UNIX username and password corresponding to
    the user you used to log in to hpc-pegasus.usc.edu .</p>
<p>The Dashboard's home page lists all workflows, which have been run
    by the current-user. The home page shows the status of each workflow i.e.
    Running/Successful/Failed/Failing. The home page lists only the top level
    workflows (Pegasus supports hierarchical workflows i.e. workflows within a
    workflow). The rows in the table are color coded</p>
<div class="itemizedlist"><ul class="itemizedlist" style="list-style-type: disc; ">
<li class="listitem"><p><span class="bold"><strong>Green</strong></span>: indicates workflow
        finished successfully.</p></li>
<li class="listitem"><p><span class="bold"><strong>Red</strong></span>: indicates workflow
        finished with a failure.</p></li>
<li class="listitem"><p><span class="bold"><strong>Blue</strong></span>: indicates a workflow is
        currently running.</p></li>
<li class="listitem"><p><span class="bold"><strong>Gray</strong></span>: indicates a workflow that
        was archived.</p></li>
</ul></div>
<div class="figure">
<a name="idm523385499680"></a><p class="title"><b>Figure 1.11. Dashboard Home Page</b></p>
<div class="figure-contents"><div class="mediaobject"><table border="0" summary="manufactured viewport for HTML img" style="cellpadding: 0; cellspacing: 0;" width="100%"><tr><td><img src="images/dashboard_home.png" width="100%" alt="Dashboard Home Page"></td></tr></table></div></div>
</div>
<br class="figure-break"><p>To view details specific to a workflow, the user can click on
    corresponding workflow label. The workflow details page lists workflow
    specific information like workflow label, workflow status, location of the
    submit directory, etc. The details page also displays pie charts showing
    the distribution of jobs based on status.</p>
<p>In addition, the details page displays a tab listing all
    sub-workflows and their statuses. Additional tabs exist which list
    information for all running, failed, successful, and failing jobs.</p>
<p>The information displayed for a job depends on it's status. For
    example, the failed jobs tab displays the job name, exit code, links to
    available standard output, and standard error contents.</p>
<div class="figure">
<a name="idm523385495024"></a><p class="title"><b>Figure 1.12. Dashboard Workflow Page</b></p>
<div class="figure-contents"><div class="mediaobject"><table border="0" summary="manufactured viewport for HTML img" style="cellpadding: 0; cellspacing: 0;" width="100%"><tr><td><img src="images/dashboard_workflow_details.png" width="100%" alt="Dashboard Workflow Page"></td></tr></table></div></div>
</div>
<br class="figure-break"><p>To view details specific to a job the user can click on the
    corresponding job's job label. The job details page lists information
    relevant to a specific job. For example, the page lists information like
    job name, exit code, run time, etc.</p>
<p>The job instance section of the job details page lists all attempts
    made to run the job i.e. if a job failed in its first attempt due to
    transient errors, but ran successfully when retried, the job instance
    section shows two entries; one for each attempt to run the job.</p>
<p>The job details page also shows tab's for failed, and successful
    task invocations (Pegasus allows users to group multiple smaller task's
    into a single job i.e. a job may consist of one or more tasks)</p>
<div class="figure">
<a name="idm523385490368"></a><p class="title"><b>Figure 1.13. Dashboard Job Description Page</b></p>
<div class="figure-contents"><div class="mediaobject"><table border="0" summary="manufactured viewport for HTML img" style="cellpadding: 0; cellspacing: 0;" width="100%"><tr><td><img src="images/dashboard_job_details.png" width="100%" alt="Dashboard Job Description Page"></td></tr></table></div></div>
</div>
<br class="figure-break"><p>The task invocation details page provides task specific information
    like task name, exit code, duration etc. Task details differ from job
    details, as they are more granular in nature.</p>
<div class="figure">
<a name="idm523385487072"></a><p class="title"><b>Figure 1.14. Dashboard Invocation Page</b></p>
<div class="figure-contents"><div class="mediaobject"><table border="0" summary="manufactured viewport for HTML img" style="cellpadding: 0; cellspacing: 0;" width="100%"><tr><td><img src="images/dashboard_invocation_details.png" width="100%" alt="Dashboard Invocation Page"></td></tr></table></div></div>
</div>
<br class="figure-break"><p>The dashboard also has web pages for workflow statistics and
    workflow charts, which graphically renders information provided by the
    pegasus-statistics and pegasus-plots command respectively.</p>
<p>The Statistics page shows the following statistics.</p>
<div class="orderedlist"><ol class="orderedlist" type="1">
<li class="listitem"><p>Workflow level statistics</p></li>
<li class="listitem"><p>Job breakdown statistics</p></li>
<li class="listitem"><p>Job specific statistics</p></li>
</ol></div>
<div class="figure">
<a name="idm523385479856"></a><p class="title"><b>Figure 1.15. Dashboard Statistics Page</b></p>
<div class="figure-contents"><div class="mediaobject"><table border="0" summary="manufactured viewport for HTML img" style="cellpadding: 0; cellspacing: 0;" width="100%"><tr><td><img src="images/dashboard_statistics.png" width="100%" alt="Dashboard Statistics Page"></td></tr></table></div></div>
</div>
<br class="figure-break">
</div><div class="navfooter">
<hr>
<table width="100%" summary="Navigation footer">
<tr>
<td width="40%" align="left">
<a accesskey="p" href="tutorial_configuration.php">Prev</a> </td>
<td width="20%" align="center"><a accesskey="u" href="tutorial.php">Up</a></td>
<td width="40%" align="right"> <a accesskey="n" href="tutorial_monitoring_cmd_tools.php">Next</a>
</td>
</tr>
<tr>
<td width="40%" align="left" valign="top">1.7. Configuring Pegasus </td>
<td width="20%" align="center"><a accesskey="h" href="index.php">Table of Contents</a></td>
<td width="40%" align="right" valign="top"> 1.9. Command line tools for Monitoring and Debugging</td>
</tr>
</table>
</div><?php  
            pegasus_footer();
        ?>
