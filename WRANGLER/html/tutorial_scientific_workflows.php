<?php  
            require('/srv/new-pegasus.isi.edu/includes/common.php'); 
            pegasus_header("1.3. What are Scientific Workflows");
        ?><div class="breadcrumbs">
<span class="breadcrumb-link"><a href="index.php">Scientific Workflows through Pegasus WMS at USC HPC Cluster</a></span> &gt; <span class="breadcrumb-link"><a href="tutorial.php">Tutorial</a></span> &gt; <span class="breadcrumb-node">What are Scientific Workflows</span>
</div><hr><div class="section">
<div class="titlepage"><div><div><h2 class="title" style="clear: both">
<a name="tutorial_scientific_workflows"></a>1.3. What are Scientific Workflows</h2></div></div></div>
<p>Scientific workflows allow users to easily express multi-step
    computational tasks, for example retrieve data from an instrument or a
    database, reformat the data, and run an analysis. A scientific workflow
    describes the dependencies between the tasks and in most cases the
    workflow is described as a directed acyclic graph (DAG), where the nodes
    are tasks and the edges denote the task dependencies. A defining property
    for a scientific workflow is that it manages data flow. The tasks in a
    scientific workflow can be everything from short serial tasks to very
    large parallel tasks (MPI for example) surrounded by a large number of
    small, serial tasks used for pre- and post-processing.</p>
<p>Workflows can vary from simple to complex. Below are some examples.
    In the figures below, the task are designated by circles/ellipses while
    the files created by the tasks are indicated by rectangles. Arrows
    indicate task dependencies.</p>
<p><span class="bold"><strong>Process Workflow</strong></span></p>
<p>It consists of a single task that runs the <code class="literal">ls</code>
    command and generates a listing of the files in the `/` directory.</p>
<div class="figure">
<a name="idm523395095920"></a><p class="title"><b>Figure 1.1. Process Workflow</b></p>
<div class="figure-contents"><div class="mediaobject" align="center"><img src="images/tutorial-single-job-wf.jpg" align="middle" height="180" alt="Process Workflow"></div></div>
</div>
<br class="figure-break"><p><span class="bold"><strong>Pipeline of Tasks</strong></span></p>
<p>The pipeline workflow consists of two tasks linked together in a
    pipeline. The first job runs the `curl` command to fetch the Pegasus home
    page and store it as an HTML file. The result is passed to the `wc`
    command, which counts the number of lines in the HTML file. </p>
<div class="figure">
<a name="idm523395091632"></a><p class="title"><b>Figure 1.2. Pipeline of Tasks</b></p>
<div class="figure-contents"><div class="mediaobject" align="center"><img src="images/tutorial-pipeline-tasks-wf.jpg" align="middle" height="252" alt="Pipeline of Tasks"></div></div>
</div>
<p><br class="figure-break"></p>
<p><span class="bold"><strong>Split Workflow</strong></span></p>
<p>The split workflow downloads the Pegasus home page using the `curl`
    command, then uses the `split` command to divide it into 4 pieces. The
    result is passed to the `wc` command to count the number of lines in each
    piece.</p>
<div class="figure">
<a name="idm523395087408"></a><p class="title"><b>Figure 1.3. Split Workflow</b></p>
<div class="figure-contents"><div class="mediaobject" align="center"><img src="images/tutorial-split-wf.jpg" align="middle" width="378" alt="Split Workflow"></div></div>
</div>
<p><br class="figure-break"></p>
<p><span class="bold"><strong>Merge Workflow</strong></span></p>
<p>The merge workflow runs the `ls` command on several */bin
    directories and passes the results to the `cat` command, which merges the
    files into a single listing. The merge workflow is an example of a
    parameter sweep over arguments.</p>
<div class="figure">
<a name="idm523395083200"></a><p class="title"><b>Figure 1.4. Merge Workflow</b></p>
<div class="figure-contents"><div class="mediaobject" align="center"><img src="images/tutorial-merge-wf.jpg" align="middle" width="378" alt="Merge Workflow"></div></div>
</div>
<p><br class="figure-break"></p>
<p><span class="bold"><strong>Diamond Workflow</strong></span></p>
<p>The diamond workflow runs combines the split and merge workflow
    patterns to create a more complex workflow.</p>
<div class="figure">
<a name="idm523395078976"></a><p class="title"><b>Figure 1.5. Diamond Workflow</b></p>
<div class="figure-contents"><div class="mediaobject" align="center"><img src="images/tutorial-diamond-wf.jpg" align="middle" width="378" alt="Diamond Workflow"></div></div>
</div>
<br class="figure-break"><p><span class="bold"><strong>Complex Workflows</strong></span></p>
<p>The above examples can be used as building blocks for much complex
    workflows. Some of these are showcased on the <a class="ulink" href="https://pegasus.isi.edu/applications" target="_top">Pegasus Applications
    page</a>.</p>
</div><div class="navfooter">
<hr>
<table width="100%" summary="Navigation footer">
<tr>
<td width="40%" align="left">
<a accesskey="p" href="tutorial_started.php">Prev</a> </td>
<td width="20%" align="center"><a accesskey="u" href="tutorial.php">Up</a></td>
<td width="40%" align="right"> <a accesskey="n" href="tutorial_submitting_wf.php">Next</a>
</td>
</tr>
<tr>
<td width="40%" align="left" valign="top">1.2. Getting Started </td>
<td width="20%" align="center"><a accesskey="h" href="index.php">Table of Contents</a></td>
<td width="40%" align="right" valign="top"> 1.4. Submitting an Example Workflow</td>
</tr>
</table>
</div><?php  
            pegasus_footer();
        ?>
