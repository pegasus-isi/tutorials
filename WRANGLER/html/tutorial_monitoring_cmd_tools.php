<?php  
            require('/srv/new-pegasus.isi.edu/includes/common.php'); 
            pegasus_header("1.9. Command line tools for Monitoring and Debugging");
        ?><div class="breadcrumbs">
<span class="breadcrumb-link"><a href="index.php">Scientific Workflows through Pegasus WMS at USC HPC Cluster</a></span> &gt; <span class="breadcrumb-link"><a href="tutorial.php">Tutorial</a></span> &gt; <span class="breadcrumb-node">Command line tools for Monitoring and Debugging</span>
</div><hr><div class="section">
<div class="titlepage"><div><div><h2 class="title" style="clear: both">
<a name="tutorial_monitoring_cmd_tools"></a>1.9. Command line tools for Monitoring and Debugging</h2></div></div></div>
<div class="toc"><dl class="toc">
<dt><span class="section"><a href="tutorial_monitoring_cmd_tools.php#idm523385472352">1.9.1. pegasus-status - monitoring the workflow</a></span></dt>
<dt><span class="section"><a href="tutorial_monitoring_cmd_tools.php#idm523385461824">1.9.2. pegasus-analyzer - debug a failed workflow</a></span></dt>
<dt><span class="section"><a href="tutorial_monitoring_cmd_tools.php#idm523385451120">1.9.3. pegasus-statistics - collect statistics about a workflow
      run</a></span></dt>
</dl></div>
<p>Pegasus also comes with a series of command line tools that users
    can use to monitor and debug their workflows.</p>
<div class="itemizedlist"><ul class="itemizedlist" style="list-style-type: disc; ">
<li class="listitem"><p>pegasus-status : monitor the status of the workflow</p></li>
<li class="listitem"><p>pegasus-analyzer : debug a failed workflow</p></li>
<li class="listitem"><p>pegasus-statistics : generate statistics from a workflow
        run.</p></li>
</ul></div>
<div class="section">
<div class="titlepage"><div><div><h3 class="title">
<a name="idm523385472352"></a>1.9.1. pegasus-status - monitoring the workflow</h3></div></div></div>
<p>After the workflow has been submitted you can monitor it using the
      <code class="literal">pegasus-status</code> command:</p>
<pre class="programlisting">$  pegasus-status -l ./submit/trainXX/pegasus/split/run0001 
STAT  IN_STATE  JOB                                                                           
Run      03:57  split-0 ( ./submit/trainXX/pegasus/split/run0001 )
Summary: 1 Condor job total (R:1)

UNRDY READY   PRE  IN_Q  POST  DONE  FAIL %DONE STATE   DAGNAME                                 
    1     2     0     0     0    12     0  80.0 Running *split-0.dag    </pre>
<p>This command shows the workflow (split-0) and the running jobs .
      It also gives statistics on the number of jobs in each state and the
      percentage of the jobs in the workflow that have finished
      successfully.</p>
<p>Use the <code class="literal">watch</code> option to continuously monitor
      the workflow:</p>
<pre class="programlisting">$ <span class="bold"><strong>pegasus-status -w submit/trainXX/pegasus/split/run0001</strong></span>
...</pre>
<p>You should see all of the jobs in the workflow run one after the
      other. After a few minutes you will see:</p>
<pre class="programlisting">(no matching jobs found in Condor Q)
UNRDY READY   PRE  IN_Q  POST  DONE  FAIL %DONE STATE   DAGNAME                                 
    0     0     0     0     0    15     0 100.0 Success *split-0.dag      </pre>
<p>That means the workflow is finished successfully.</p>
<p>If the workflow finished successfully you should see the output
      count files in the <code class="filename">output</code> directory.</p>
<pre class="programlisting">$ <span class="bold"><strong>ls output/</strong></span>
count.txt.a  count.txt.b  count.txt.c  count.txt.d
</pre>
</div>
<div class="section">
<div class="titlepage"><div><div><h3 class="title">
<a name="idm523385461824"></a>1.9.2. pegasus-analyzer - debug a failed workflow</h3></div></div></div>
<p>In the case that one or more jobs fails, then the output of the
      <code class="literal">pegasus-status</code> command above will have a non-zero
      value in the <code class="literal">FAILURE</code> column.</p>
<p>You can debug the failure using the
      <code class="literal">pegasus-analyzer</code> command. This command will identify
      the jobs that failed and show their output. Because the workflow
      succeeded, <code class="literal">pegasus-analyzer</code> will only show some basic
      statistics about the number of successful jobs:</p>
<pre class="programlisting">$ <span class="bold"><strong>pegasus-analyzer ./submit/trainXX/pegasus/split/run0001/
</strong></span>
************************************Summary*************************************

 Submit Directory   : ./submit/trainXX/pegasus/split/run0001/
 Total jobs         :     15 (100.00%)
 # jobs succeeded   :     15 (100.00%)
 # jobs failed      :      0 (0.00%)
 # jobs unsubmitted :      0 (0.00%)

</pre>
<p>If the workflow had failed you would see something like
      this:</p>
<pre class="programlisting">$  <span class="bold"><strong>pegasus-analyzer submit/trainXX/pegasus/split/run0002
</strong></span>
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
2016-03-17 18:52:45,027    INFO:  <span class="bold"><strong>/bin/cp: cannot stat `/auto/rcf-40/trainXX/tutorial/split/input/pegasus.html': No such file or directory</strong></span>
2016-03-17 18:52:45,028   ERROR:  Command exited with non-zero exit code (1): /bin/cp ...
2016-03-17 18:52:45,029    INFO:  --------------------------------------------------------------------------------
2016-03-17 18:52:45,029    INFO:  Stats: Total 3 transfers, 0.0 B transferred in 188 seconds. Rate: 0.0 B/s (0.0 b/s)
2016-03-17 18:52:45,029    INFO:         Between sites local-&gt;usc_hpcc : 3 transfers, 0.0 B transferred in 188 seconds. Rate: 0.0 B/s (0.0 b/s)
2016-03-17 18:52:45,030 CRITICAL:  Some transfers failed! See above, and possibly stderr.


</pre>
<p>In this example, we removed one of the input files. We will cover
      this in more detail in the recovery section. The output of
      <code class="literal">pegasus-analyzer</code> indicates that pegasus.html file
      could not be found.</p>
</div>
<div class="section">
<div class="titlepage"><div><div><h3 class="title">
<a name="idm523385451120"></a>1.9.3. pegasus-statistics - collect statistics about a workflow
      run</h3></div></div></div>
<p>The <code class="literal">pegasus-statistics</code> command can be used to
      gather statistics about the runtime of the workflow and its jobs. The
      <code class="literal">-s all</code> argument tells the program to generate all
      statistics it knows how to calculate:</p>
<pre class="programlisting">$  pegasus-statistics -s all submit/trainXX/pegasus/split/run0001

#
# Pegasus Workflow Management System - http://pegasus.isi.edu
#
# Workflow summary:
#   Summary of the workflow execution. It shows total
#   tasks/jobs/sub workflows run, how many succeeded/failed etc.
#   In case of hierarchical workflow the calculation shows the
#   statistics across all the sub workflows.It shows the following
#   statistics about tasks, jobs and sub workflows.
#     * Succeeded - total count of succeeded tasks/jobs/sub workflows.
#     * Failed - total count of failed tasks/jobs/sub workflows.
#     * Incomplete - total count of tasks/jobs/sub workflows that are
#       not in succeeded or failed state. This includes all the jobs
#       that are not submitted, submitted but not completed etc. This
#       is calculated as  difference between 'total' count and sum of
#       'succeeded' and 'failed' count.
#     * Total - total count of tasks/jobs/sub workflows.
#     * Retries - total retry count of tasks/jobs/sub workflows.
#     * Total+Retries - total count of tasks/jobs/sub workflows executed
#       during workflow run. This is the cumulative of retries,
#       succeeded and failed count.
# Workflow wall time:
#   The wall time from the start of the workflow execution to the end as
#   reported by the DAGMAN.In case of rescue dag the value is the
#   cumulative of all retries.
# Cumulative job wall time:
#   The sum of the wall time of all jobs as reported by kickstart.
#   In case of job retries the value is the cumulative of all retries.
#   For workflows having sub workflow jobs (i.e SUBDAG and SUBDAX jobs),
#   the wall time value includes jobs from the sub workflows as well.
# Cumulative job wall time as seen from submit side:
#   The sum of the wall time of all jobs as reported by DAGMan.
#   This is similar to the regular cumulative job wall time, but includes
#   job management overhead and delays. In case of job retries the value
#   is the cumulative of all retries. For workflows having sub workflow
#   jobs (i.e SUBDAG and SUBDAX jobs), the wall time value includes jobs
#   from the sub workflows as well.
# Cumulative job badput wall time:
#   The sum of the wall time of all failed jobs as reported by kickstart.
#   In case of job retries the value is the cumulative of all retries.
#   For workflows having sub workflow jobs (i.e SUBDAG and SUBDAX jobs),
#   the wall time value includes jobs from the sub workflows as well.
# Cumulative job badput wall time as seen from submit side:
#   The sum of the wall time of all failed jobs as reported by DAGMan.
#   This is similar to the regular cumulative job badput wall time, but includes
#   job management overhead and delays. In case of job retries the value
#   is the cumulative of all retries. For workflows having sub workflow
#   jobs (i.e SUBDAG and SUBDAX jobs), the wall time value includes jobs
#   from the sub workflows as well.
------------------------------------------------------------------------------
Type           Succeeded Failed  Incomplete  Total     Retries   Total+Retries
Tasks          5         0       0           5         0         5            
Jobs           15        0       0           15        0         15           
Sub-Workflows  0         0       0           0         0         0            
------------------------------------------------------------------------------

Workflow wall time                                       : 4 mins, 31 secs
Cumulative job wall time                                 : 34 secs
Cumulative job wall time as seen from submit side        : 25 secs
Cumulative job badput wall time                          : 
Cumulative job badput wall time as seen from submit side : 

Summary                       : submit/trainXX/pegasus/split/run0001/statistics/summary.txt
Workflow execution statistics : submit/trainXX/pegasus/split/run0001/statistics/workflow.txt
Job instance statistics       : submit/trainXX/pegasus/split/run0001/statistics/jobs.txt
Transformation statistics     : submit/trainXX/pegasus/split/run0001/statistics/breakdown.txt
Time statistics               : submit/trainXX/pegasus/split/run0001/statistics/time.txt



</pre>
<p>The output of <code class="literal">pegasus-statistics</code> contains many
      definitions to help users understand what all of the values reported
      mean. Among these are the total wall time of the workflow, which is the
      time from when the workflow was submitted until it finished, and the
      total cumulative job wall time, which is the sum of the runtimes of all
      the jobs.</p>
<p>The <code class="literal">pegasus-statistics</code> command also writes out
      several reports in the <code class="filename">statistics</code> subdirectory of
      the workflow submit directory:</p>
<pre class="programlisting">$ <span class="bold"><strong>ls submit/trainXX/pegasus/split/run0001/statistics/</strong></span>
jobs.txt          summary.txt         time.txt          breakdown.txt          workflow.txt</pre>
<p>The file <code class="filename">breakdown.txt</code>, for example, has min,
      max, and mean runtimes for each transformation:</p>
<pre class="programlisting">$ <span class="bold"><strong>more </strong></span> <span class="bold"><strong>submit/trainXX/pegasus/split/run0001/statistics/breakdown.txt 
</strong></span>
# Transformation - name of the transformation.
# Count          - the number of times the invocations corresponding to
#                  the transformation was executed.
# Succeeded      - the count of the succeeded invocations corresponding
#                  to the transformation.
# Failed         - the count of the failed invocations corresponding to
#                  the transformation.
# Min(sec)       - the minimum invocation runtime value corresponding
#                  to the transformation.
# Max(sec)       - the maximum invocation runtime value corresponding
#                  to the transformation.
# Mean(sec)      - the mean of the invocation runtime corresponding
#                  to the transformation.
# Total(sec)     - the cumulative of invocation runtime corresponding
#                  to the transformation.

# dbeb6c46-0960-466f-855b-d517748835f0 (split)
Transformation           Count     Succeeded Failed  Min       Max       Mean          Total     
dagman::post             15        15        0       5.0       5.0       5.0           75.0      
pegasus::cleanup         6         6         0       2.249     4.247     3.579         21.475    
pegasus::dirmanager      1         1         0       2.273     2.273     2.273         2.273     
pegasus::transfer        3         3         0       2.254     4.241     3.573         10.719    
split                    1         1         0       0.02      0.02      0.02          0.02      
wc                       4         4         0       0.004     0.004     0.004         0.016     


# All (All)
Transformation           Count     Succeeded Failed  Min       Max       Mean          Total     
dagman::post             15        15        0       5.0       5.0       5.0           75.0      
pegasus::cleanup         6         6         0       2.249     4.247     3.579         21.475    
pegasus::dirmanager      1         1         0       2.273     2.273     2.273         2.273     
pegasus::transfer        3         3         0       2.254     4.241     3.573         10.719    
split                    1         1         0       0.02      0.02      0.02          0.02      
wc                       4         4         0       0.004     0.004     0.004         0.016     
  
</pre>
<p>In this case, because the example transformation sleeps for 30
      seconds, the min, mean, and max runtimes for each of the analyze,
      findrange, and preprocess transformations are all close to 30.</p>
</div>
</div><div class="navfooter">
<hr>
<table width="100%" summary="Navigation footer">
<tr>
<td width="40%" align="left">
<a accesskey="p" href="tutorial_wf_dashboard.php">Prev</a> </td>
<td width="20%" align="center"><a accesskey="u" href="tutorial.php">Up</a></td>
<td width="40%" align="right"> <a accesskey="n" href="tutorial_failure_recovery.php">Next</a>
</td>
</tr>
<tr>
<td width="40%" align="left" valign="top">1.8. Workflow Dashboard for Monitoring and Debugging </td>
<td width="20%" align="center"><a accesskey="h" href="index.php">Table of Contents</a></td>
<td width="40%" align="right" valign="top"> 1.10. Recovery from Failures</td>
</tr>
</table>
</div><?php  
            pegasus_footer();
        ?>
