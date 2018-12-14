<?php  
            require('/srv/new-pegasus.isi.edu/includes/common.php'); 
            pegasus_header("1.11. Running the whole workflow as an MPI job");
        ?><div class="breadcrumbs">
<span class="breadcrumb-link"><a href="index.php">Scientific Workflows through Pegasus WMS at USC HPC Cluster</a></span> &gt; <span class="breadcrumb-link"><a href="tutorial.php">Tutorial</a></span> &gt; <span class="breadcrumb-node">Running the whole workflow as an MPI job</span>
</div><hr><div class="section">
<div class="titlepage"><div><div><h2 class="title" style="clear: both">
<a name="tutorial_pmc"></a>1.11. Running the whole workflow as an MPI job</h2></div></div></div>
<p>Often, users have lots of short running single processor jobs in
    their workflow, that if submitted individually to the underlying PBS
    cluster take a long time to execute, as each job sits in the PBS queue.
    For example in our previous example, each job in the blackdiamond workflow
    actually runs for a minute each. However, since each job is submitted as a
    separate job to PBS, each job sits in the cluster PBS queue before it is
    executed. In order to alleviate this, it makes sense to cluster the short
    running jobs together. Pegasus allows users to cluster tasks in their
    workflow into larger chunks, and then execute them using a MPI based
    master worker tool called <span class="emphasis"><em><span class="bold"><strong>pegasus-mpi-cluster</strong></span></em></span> .</p>
<p>In this example, we take the same blackdiamond workflow that we ran
    previously and now run it using PMC where the whole workflow is clustered
    into a single MPI job. In order to tell Pegasus to cluster the jobs we
    have to do the following</p>
<div class="orderedlist"><ol class="orderedlist" type="1">
<li class="listitem">
<p>Tell Pegasus what jobs are clustered. In this example, we do it
        by annotating the DAX with a special pegasus profile called label. In
        the DAX generator daxgen.py you will see the following</p>
<pre class="programlisting">        wc = Job("wc")
        wc.addArguments("-l",part)

        <span class="bold"><strong>//associate the label with the job. all jobs with same label
        //are run with PMC when doing job clustering</strong></span>
        wc.addProfile( Profile("pegasus","label","p1"))
        </pre>
</li>
<li class="listitem">
<p>Tell pegasus that it has to do job clustering and what
        executable to use for job clustering.</p>
<p>To do this, you do the following</p>
<div class="itemizedlist"><ul class="itemizedlist" style="list-style-type: disc; ">
<li class="listitem"><p>In pegasus.properties file specify the property <span class="bold"><strong>pegasus.job.aggregator mpiexec</strong></span></p></li>
<li class="listitem">
<p>In the transformation catalog, specify the path to the
            clustering executable. In this case, it is a wrapper around PMC
            that does mpiexec on pegasus-mpi-cluster. In conf/tc.dat you can
            see the last entry as</p>
<pre class="programlisting">$ <span class="bold"><strong>tail conf/tc.dat</strong></span>

# pegasus mpi clustering executable
tr pegasus::mpiexec{
   site usc-hpcc {
        pfn "/home/rcf-proj/gmj/pegasus/SOFTWARE/pegasus/pegasus-mpi-cluster-wrapper"
        arch "x86_64"
        os "LINUX"

        type "INSTALLED"
        profile pegasus "clusters.size" "2" 

        # the various parameters to specify the size of the MPI job
        # in which the workflow runs on the cluster
        profile globus "jobtype" "mpi"

        # This specifies the maximum runtime for the job in seconds. It
        # should be an integer value. Pegasus converts it to the
        # "hh:mm:ss" format required by PBS.
        profile pegasus "runtime" "600"

        # specfiy the ppn parameter.
        profile pegasus "ppn" "4"

        # specify the nodes parameter
        profile pegasus "nodes" "1"

        #specify the pmem parameter
        profile pegasus "memory" "1gb"


    }
}
</pre>
<p>The profiles tell Pegasus that the PMC executable needs to
            be run on 4 processors on a single node, process per process as
            1GB and total memory on the node as 16GB.</p>
</li>
</ul></div>
</li>
<li class="listitem">
<p>Lastly, while planning the workflow we add <span class="bold"><strong>--cluster </strong></span>option to pegasus-plan. That is what
        we have in plan_cluster_dax.sh file.</p>
<pre class="programlisting">$ <span class="bold"><strong>cat plan_cluster_dax.sh</strong></span>

#!/bin/sh

set -e
...

# This command tells Pegasus to plan the workflow contained in 
# dax file passed as an argument. The planned workflow will be stored
# in the "submit" directory. The execution # site is "".
# --input-dir tells Pegasus where to find workflow input files.
# --output-dir tells Pegasus where to place workflow output files.
pegasus-plan --conf pegasus.properties \
    --dax $DAXFILE \
    --dir $DIR/submit \
    --input-dir $DIR/input \
    --output-dir $DIR/output \
    --cleanup leaf \
    --cluster label \
    --force \
    --sites usc-hpcc \
    --submit
</pre>
</li>
</ol></div>
<p><span class="bold"><strong>Let us now plan and run the
    workflow.</strong></span></p>
<pre class="programlisting">$ <span class="bold"><strong>./plan_cluster_dax.sh split.dax </strong></span>
2016.03.17 19:00:26.835 PDT:    
2016.03.17 19:00:26.841 PDT:   ----------------------------------------------------------------------- 
2016.03.17 19:00:26.846 PDT:   File for submitting this DAG to Condor           : split-0.dag.condor.sub 
2016.03.17 19:00:26.851 PDT:   Log of DAGMan debugging messages                 : split-0.dag.dagman.out 
2016.03.17 19:00:26.857 PDT:   Log of Condor library output                     : split-0.dag.lib.out 
2016.03.17 19:00:26.862 PDT:   Log of Condor library error messages             : split-0.dag.lib.err 
2016.03.17 19:00:26.867 PDT:   Log of the life of condor_dagman itself          : split-0.dag.dagman.log 
2016.03.17 19:00:26.873 PDT:    
2016.03.17 19:00:26.888 PDT:   ----------------------------------------------------------------------- 
2016.03.17 19:00:28.295 PDT:   Your database is compatible with Pegasus version: 4.6.1 
2016.03.17 19:00:28.470 PDT:   Submitting to condor split-0.dag.condor.sub 
2016.03.17 19:00:28.535 PDT:   Submitting job(s). 
2016.03.17 19:00:28.540 PDT:   1 job(s) submitted to cluster 8523. 
2016.03.17 19:00:28.545 PDT:    
2016.03.17 19:00:28.551 PDT:   Your workflow has been started and is running in the base directory: 
2016.03.17 19:00:28.556 PDT:    
2016.03.17 19:00:28.561 PDT:     ./submit/trainXX/pegasus/split/run0003 
2016.03.17 19:00:28.567 PDT:    
2016.03.17 19:00:28.572 PDT:   *** To monitor the workflow you can run *** 
2016.03.17 19:00:28.577 PDT:    
2016.03.17 19:00:28.583 PDT:     pegasus-status -l ./submit/trainXX/pegasus/split/run0003 
2016.03.17 19:00:28.588 PDT:    
2016.03.17 19:00:28.593 PDT:   *** To remove your workflow run *** 
2016.03.17 19:00:28.599 PDT:    
2016.03.17 19:00:28.604 PDT:     pegasus-remove ./submit/trainXX/pegasus/split/run0003 
2016.03.17 19:00:28.609 PDT:    
2016.03.17 19:00:28.737 PDT:   Time taken to execute is 3.371 seconds 
</pre>
<p>This is what the diamond workflow looks like after Pegasus has
    finished planning the DAX:</p>
<div class="figure">
<a name="idm523385398704"></a><p class="title"><b>Figure 1.16. Clustered Diamond DAG</b></p>
<div class="figure-contents"><div class="mediaobject"><img src="images/split-pmc.png" width="378" alt="Clustered Diamond DAG"></div></div>
</div>
<br class="figure-break"><p>You can see that instead of 4 jobs making up the diamond have been
    replaced by a single merge_p1 job, that is executed as a MPI job.</p>
</div><div class="navfooter">
<hr>
<table width="100%" summary="Navigation footer">
<tr>
<td width="40%" align="left">
<a accesskey="p" href="tutorial_failure_recovery.php">Prev</a> </td>
<td width="20%" align="center"><a accesskey="u" href="tutorial.php">Up</a></td>
<td width="40%" align="right"> <a accesskey="n" href="tutorial_conclusion.php">Next</a>
</td>
</tr>
<tr>
<td width="40%" align="left" valign="top">1.10. Recovery from Failures </td>
<td width="20%" align="center"><a accesskey="h" href="index.php">Table of Contents</a></td>
<td width="40%" align="right" valign="top"> 1.12. Conclusion</td>
</tr>
</table>
</div><?php  
            pegasus_footer();
        ?>
