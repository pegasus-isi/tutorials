<?php  
            require('/srv/new-pegasus.isi.edu/includes/common.php'); 
            pegasus_header("1.10. Recovery from Failures");
        ?><div class="breadcrumbs">
<span class="breadcrumb-link"><a href="index.php">Scientific Workflows through Pegasus WMS at USC HPC Cluster</a></span> &gt; <span class="breadcrumb-link"><a href="tutorial.php">Tutorial</a></span> &gt; <span class="breadcrumb-node">Recovery from Failures</span>
</div><hr><div class="section">
<div class="titlepage"><div><div><h2 class="title" style="clear: both">
<a name="tutorial_failure_recovery"></a>1.10. Recovery from Failures</h2></div></div></div>
<div class="toc"><dl class="toc"><dt><span class="section"><a href="tutorial_failure_recovery.php#idm523385435792">1.10.1. Submitting Rescue Workflows</a></span></dt></dl></div>
<p>Executing workflows in a distributed environment can lead to
    failures. Often, they are a result of the underlying infrastructure being
    temporarily unavailable, or errors in workflow setup such as incorrect
    executables specified, or input files being unavailable.</p>
<p>In case of transient infrastructure failures such as a node being
    temporarily down in a cluster, Pegasus will automatically retry jobs in
    case of failure. After a set number of retries (usually once), a hard
    failure occurs, because of which workflow will eventually fail.</p>
<p>In most of the cases, these errors are correctable (either the
    resource comes back online or application errors are fixed). Once the
    errors are fixed, you may not want to start a new workflow but instead
    start from the point of failure. In order to do this, you can submit the
    rescue workflows automatically created in case of failures. A rescue
    workflow contains only a description of only the work that remains to be
    done.</p>
<div class="section">
<div class="titlepage"><div><div><h3 class="title">
<a name="idm523385435792"></a>1.10.1. Submitting Rescue Workflows</h3></div></div></div>
<p>In this example, we will take our previously run workflow and
      introduce errors such that workflow we just executed fails at
      runtime.</p>
<p>First we will "hide" the input file to cause a failure by renaming
      it:</p>
<pre class="programlisting">$ <span class="bold"><strong>mv input/pegasus.html input/pegasus.html.bak</strong></span>
      </pre>
<p>Now submit the workflow again:</p>
<pre class="programlisting">$ <span class="bold"><strong>./plan_dax.sh split.dax </strong></span>
2016.03.17 18:45:40.465 PDT:    
2016.03.17 18:45:40.470 PDT:   ----------------------------------------------------------------------- 
2016.03.17 18:45:40.476 PDT:   File for submitting this DAG to Condor           : split-0.dag.condor.sub 
2016.03.17 18:45:40.481 PDT:   Log of DAGMan debugging messages                 : split-0.dag.dagman.out 
2016.03.17 18:45:40.486 PDT:   Log of Condor library output                     : split-0.dag.lib.out 
2016.03.17 18:45:40.492 PDT:   Log of Condor library error messages             : split-0.dag.lib.err 
2016.03.17 18:45:40.497 PDT:   Log of the life of condor_dagman itself          : split-0.dag.dagman.log 
2016.03.17 18:45:40.502 PDT:    
2016.03.17 18:45:40.518 PDT:   ----------------------------------------------------------------------- 
2016.03.17 18:45:41.694 PDT:   Your database is compatible with Pegasus version: 4.6.1 
2016.03.17 18:45:41.865 PDT:   Submitting to condor split-0.dag.condor.sub 
2016.03.17 18:45:41.926 PDT:   Submitting job(s). 
2016.03.17 18:45:41.932 PDT:   1 job(s) submitted to cluster 8511. 
2016.03.17 18:45:41.937 PDT:    
2016.03.17 18:45:41.942 PDT:   Your workflow has been started and is running in the base directory: 
2016.03.17 18:45:41.948 PDT:    
2016.03.17 18:45:41.953 PDT:     ./submit/trainXX/pegasus/split/run0002 
2016.03.17 18:45:41.958 PDT:    
2016.03.17 18:45:41.964 PDT:   *** To monitor the workflow you can run *** 
2016.03.17 18:45:41.969 PDT:    
2016.03.17 18:45:41.974 PDT:     pegasus-status -l ./split/submit/trainXX/pegasus/split/run0002 
2016.03.17 18:45:41.980 PDT:    
2016.03.17 18:45:41.985 PDT:   *** To remove your workflow run *** 
2016.03.17 18:45:41.990 PDT:    
2016.03.17 18:45:41.996 PDT:     pegasus-remove ./split/submit/trainXX/pegasus/split/run0002 
2016.03.17 18:45:42.001 PDT:    
2016.03.17 18:45:42.130 PDT:   Time taken to execute is 3.19 seconds 

</pre>
<p>We will now monitor the workflow using the pegasus-status command
      till it fails. We will add -w option to pegasus-status to watch
      automatically till the workflow finishes:</p>
<pre class="programlisting"><span class="bold"><strong>$ </strong></span><span class="bold"><strong>pegasus-status -w submit/tutorial/pegasus/split/run0002</strong></span>
(no matching jobs found in Condor Q)
UNREADY   READY     PRE  QUEUED    POST SUCCESS FAILURE %DONE
      8       0       0       0       0       2       1  18.2
Summary: 1 DAG total (Failure:1)
</pre>
<p>Now we can use the pegasus-analyzer command to determine what went
      wrong:</p>
<pre class="programlisting"><span class="bold"><strong>$ </strong></span> <span class="bold"><strong>pegasus-analyzer submit/trainXX/pegasus/split/run0002</strong></span>

************************************Summary*************************************

 Submit Directory   : submit/trainXX/pegasus/split/run0002
 Total jobs         :     15 (100.00%)
 # jobs succeeded   :      1 (6.67%)
 # jobs failed      :      1 (6.67%)
 # jobs unsubmitted :     13 (86.67%)

******************************Failed jobs' details******************************

==========================stage_in_local_usc-hpcc_0_0===========================

 last state: POST_SCRIPT_FAILED
       site: local
submit file: stage_in_local_usc-hpcc_0_0.sub
output file: stage_in_local_usc-hpcc_0_0.out.001
 error file: stage_in_local_usc-hpcc_0_0.err.001

-------------------------------Task #1 - Summary--------------------------------

site        : local
hostname    : hpc-pegasus.usc.edu
executable  : /auto/rcf-proj/gmj/pegasus/SOFTWARE/pegasus/pegasus-4.6.1dev/bin/pegasus-transfer
arguments   :   --threads   2  
exitcode    : 1
working dir : /auto/rcf-40/trainXX/tutorial/split/submit/trainXX/pegasus/split/run0002

------------------Task #1 - pegasus::transfer - None - stdout-------------------

2016-03-17 18:49:36,704    INFO:  Reading URL pairs from stdin
2016-03-17 18:49:36,706    INFO:  1 transfers loaded
2016-03-17 18:49:36,707    INFO:  PATH=/usr/bin:/bin
2016-03-17 18:49:36,707    INFO:  LD_LIBRARY_PATH=
2016-03-17 18:49:36,770    INFO:  --------------------------------------------------------------------------------
2016-03-17 18:49:36,771    INFO:  Starting transfers - attempt 1
2016-03-17 18:49:38,782    INFO:  /bin/cp -f -R -L '/auto/rcf-40/trainXX/tutorial/split/input/pegasus.html' '/auto/rcf-40/trainXX/tutorial/split/hpcc/scratch/trainXX/pegasus/split/run0002/pegasus.html'
2016-03-17 18:49:38,793    INFO:  /bin/cp: cannot stat `/auto/rcf-40/trainXX/tutorial/split/input/pegasus.html': No such file or directory
2016-03-17 18:49:38,794   ERROR:  Command exited with non-zero exit code (1): /bin/cp ...
2016-03-17 18:50:21,895    INFO:  --------------------------------------------------------------------------------
2016-03-17 18:50:21,896    INFO:  Starting transfers - attempt 2
2016-03-17 18:50:23,901    INFO:  /bin/cp -f -R -L '/auto/rcf-40/trainXX/tutorial/split/input/pegasus.html' '/auto/rcf-40/trainXX/tutorial/split/hpcc/scratch/trainXX/pegasus/split/run0002/pegasus.html'
2016-03-17 18:50:23,910    INFO:  /bin/cp: cannot stat `/auto/rcf-40/trainXX/tutorial/split/input/pegasus.html': No such file or directory
2016-03-17 18:50:23,911   ERROR:  Command exited with non-zero exit code (1): /bin/cp ...
2016-03-17 18:52:43,012    INFO:  --------------------------------------------------------------------------------
2016-03-17 18:52:43,012    INFO:  Starting transfers - attempt 3
2016-03-17 18:52:45,018    INFO:  /bin/cp -f -R -L '/auto/rcf-40/trainXX/tutorial/split/input/pegasus.html' '/auto/rcf-40/trainXX/tutorial/split/hpcc/scratch/trainXX/pegasus/split/run0002/pegasus.html'
2016-03-17 18:52:45,027    INFO:  /bin/cp: cannot stat `/auto/rcf-40/trainXX/tutorial/split/input/pegasus.html': No such file or directory
2016-03-17 18:52:45,028   ERROR:  Command exited with non-zero exit code (1): /bin/cp ...
2016-03-17 18:52:45,029    INFO:  --------------------------------------------------------------------------------
2016-03-17 18:52:45,029    INFO:  Stats: Total 3 transfers, 0.0 B transferred in 188 seconds. Rate: 0.0 B/s (0.0 b/s)
2016-03-17 18:52:45,029    INFO:         Between sites local-&gt;usc_hpcc : 3 transfers, 0.0 B transferred in 188 seconds. Rate: 0.0 B/s (0.0 b/s)
2016-03-17 18:52:45,030 CRITICAL:  Some transfers failed! See above, and possibly stderr.


</pre>
<p>The above listing indicates that it could not transfer
      pegasus.html. Let's correct that error by restoring the pegasus.html
      file:</p>
<pre class="programlisting">$ <span class="bold"><strong>mv input/pegasus.html.bak input/pegasus.html</strong></span>
      </pre>
<p>Now in order to start the workflow from where we left off, instead
      of executing pegasus-plan we will use the command pegasus-run on the
      directory from our previous failed workflow run:</p>
<pre class="programlisting">$  <span class="bold"><strong>pegasus-run submit/trainXX/pegasus/split/run0002</strong></span>
Rescued ./submit/trainXX/pegasus/split/run0002/split-0.log as ./submit/trainXX/pegasus/split/run0002/split-0.log.000
Submitting to condor split-0.dag.condor.sub
Submitting job(s).
1 job(s) submitted to cluster 8515.

Your workflow has been started and is running in the base directory:

  submit/trainXX/pegasus/split/run0002

*** To monitor the workflow you can run ***

  pegasus-status -l submit/trainXX/pegasus/split/run0002

*** To remove your workflow run ***

  pegasus-remove submit/trainXX/pegasus/split/run0002
</pre>
<p>The workflow will now run to completion and succeed.</p>
<pre class="programlisting"><span class="bold"><strong>$ pegasus-status -l </strong></span><span class="bold"><strong>submit/trainXX/pegasus/split/run0002</strong></span>
(no matching jobs found in Condor Q)
UNRDY READY   PRE  IN_Q  POST  DONE  FAIL %DONE STATE   DAGNAME                                 
    0     0     0     0     0    11     0 100.0 Success *split-0.dag                            
Summary: 1 DAG total (Success:1)
                     
</pre>
</div>
</div><div class="navfooter">
<hr>
<table width="100%" summary="Navigation footer">
<tr>
<td width="40%" align="left">
<a accesskey="p" href="tutorial_monitoring_cmd_tools.php">Prev</a> </td>
<td width="20%" align="center"><a accesskey="u" href="tutorial.php">Up</a></td>
<td width="40%" align="right"> <a accesskey="n" href="tutorial_pmc.php">Next</a>
</td>
</tr>
<tr>
<td width="40%" align="left" valign="top">1.9. Command line tools for Monitoring and Debugging </td>
<td width="20%" align="center"><a accesskey="h" href="index.php">Table of Contents</a></td>
<td width="40%" align="right" valign="top"> 1.11. Running the whole workflow as an MPI job</td>
</tr>
</table>
</div><?php  
            pegasus_footer();
        ?>
