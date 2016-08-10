This tutorial will take you through the steps of running simple workflows using 
Pegasus Workflow Management System on Bluewaters. Pegasus allows scientists to

* **Automate** their scientific computational work, as portable workflows.
  Pegasus enables scientists to construct workflows in abstract terms 
  without worrying about the details of the underlying execution 
  environment or the particulars of the low-level specifications required 
  by the middleware (Condor, Globus, or Amazon EC2). 
  It automatically locates the necessary input data and computational resources 
  necessary for workflow execution. It cleans up storage as the workflow is 
  executed so that data-intensive workflows have enough space to execute 
  on storage-constrained resources.

* **Recover** from failures at runtime. When errors occur, Pegasus tries to 
  recover when possible by retrying tasks, and when all else fails, provides 
  a rescue workflow containing a description of only the work that remains
  to be done. It also enables users to move computations from one resource to another.
  Pegasus keeps track of what has been done (provenance) including the 
  locations of data used and produced, and which software was used with
  which parameters.

* **Debug** failures in their computations using a set of system provided 
  debugging tools and an online workflow monitoring dashboard.

This tutorial is intended for new users who want to get a quick overview 
of Pegasus concepts and usage.  The instructions listed here refer mainly 
to the simple split MPI example. All other examples are also configured to
run via pegasus-init on Bluewaters. The tutorial covers

* submission of an already generated example workflow with Pegasus.
* the command line tools for monitoring, debugging and generating statistics.
* recovery from failures
* creation of workflow using system provided API
* information catalogs configuration.

More information about the topics covered in this tutorial can be found
in the Pegasus user guide at
 [https://pegasus.isi.edu/documentation/](https://pegasus.isi.edu/documentation/)

All of the steps in this tutorial are performed on the command-line. 
The convention we will use for command-line input and output is to put 
things that you should type in bold, monospace font, and to put the output
you should get in a normal weight, monospace font, like this:

[user@host dir]$ you type this
you get this
Where [user@host dir]$ is the terminal prompt, the text you should type 
is “you type this”, and the output you should get is "you get this". 
The terminal prompt will be abbreviated as $. Because some of the outputs
are long, we don’t always include everything. 
Where the output is truncated we will add an ellipsis '...' to indicate
the omitted output.

If you are having trouble with this tutorial, or anything else related 
to Pegasus, you can contact the Pegasus Users mailing list at 
<pegasus-users@isi.edu> to get help. You can also contact us on our
support chatroom on HipChat.

## Scientific Workflows 

Scientific workflows allow users to easily express multi-step
computational tasks, for example retrieve data from an instrument or a
database, reformat the data, and run an analysis. A scientific workflow
describes the dependencies between the tasks and in most cases the
workflow is described as a directed acyclic graph (DAG), where the
nodes are tasks and the edges denote the task dependencies. A defining
property for a scientific workflow is that it manages data flow. The
tasks in a scientific workflow can be everything from short serial tasks
to very large parallel tasks (MPI for example) surrounded by a large
number of small, serial tasks used for pre- and post-processing.


**Process Workflow**

It consists of a single task that runs the ls command and generates a listing of the files 
in the `/` directory.

![Process Workflow](https://raw.githubusercontent.com/pegasus-isi/tutorials/master/BLUEWATERS/figures/tutorial-single-job-wf.jpg)

**Pipeline of Tasks**

The pipeline workflow consists of two tasks linked together in a pipeline. 
The first job runs the `curl` command to fetch the Pegasus home page and store it as an HTML file. 
The result is passed to the `wc` command, which counts the number of lines in the HTML file.


![Pipeline of Tasks Workflow](https://raw.githubusercontent.com/pegasus-isi/tutorials/master/BLUEWATERS/figures/tutorial-pipeline-wf.jpg)

**Split Workflow**

The split workflow downloads the Pegasus home page using the `curl` command, 
then uses the `split` command to divide it into 4 pieces. The result is passed 
to the `wc` command to count the number of lines in each piece.

![Split Workflow](https://raw.githubusercontent.com/pegasus-isi/tutorials/master/BLUEWATERS/figures/tutorial-split-wf.jpg)

**Merge Workflow**

The merge workflow runs the `ls` command on several */bin directories and 
passes the results to the `cat` command, which merges the files into a single
listing. The merge workflow is an example of a parameter sweep over arguments.

![Merge Workflow](https://raw.githubusercontent.com/pegasus-isi/tutorials/master/BLUEWATERS/figures/tutorial-merge-wf.jpg)


**Diamond Workflow**

The diamond workflow runs combines the split and merge workflow patterns to create a more complex workflow.

![Diamond Workflow](https://raw.githubusercontent.com/pegasus-isi/tutorials/master/BLUEWATERS/figures/tutorial-diamond-wf.jpg)

**Complex Workflows**

The above examples can be used as building blocks for much complex workflows. 
Some of these are showcased on the [Pegasus Applications page](https://pegasus.isi.edu/application-showcase/) .


## Setup your environment for Pegasus

Before we start, lets make sure that Pegasus and HTCondor installation is
in your path. You can do that by sourcing a simple setup script
```
$ source /projects/eot/bafu/setup.sh

$ pegasus-version
4.6.2dev

$ condor_version
$CondorVersion: 8.5.5 Jul 28 2016 BuildID: BW $
$CondorPlatform: X86_64-sles_11 $

$ mkdir pegasus-tutorial
$ cd pegasus-tutorial
```


## Example 1: MPI Workflow

For this tutorial we will be using the an example MPI workflow.
This example executes a workflow consisting of a single MPI job that
executes on Bluewaters. The MPI executable is a simple hello
world MPI executable that is shipped with the example and needs to be 
compiled by each user.

This example is a canonical example, that highlights the data management 
capabilities of Pegasus, whereby as part of the workflow execution you can
retrieve input from a local/remote location , execute the jobs defined in 
the DAX ( in this case a single MPI job), ship the data out to 
local directory/remote location, and cleanup the scratch space automatically
as the workflow progresses.

The workflow can be created like this:

```
$  pegasus-init mpi
Do you want to generate a tutorial workflow? (y/n) [n]: y
1: Local Machine
2: USC HPCC Cluster
3: OSG from ISI submit node
4: XSEDE, with Bosco
5: Bluewaters, with Glite
What environment is tutorial to be setup for? (1-5) [1]: 5
1: Process
2: Pipeline
3: Split
4: Merge
5: Diamond
6: MPI Hello World
What tutorial workflow do you want? (1-6) [1]: 6
Pegasus Tutorial setup for example workflow - mpi-hw for execution on bw-glite in directory /mnt/a/u/training/instr006/pegasus-tutorial/mpi
```

Tip: The pegasus-init tool can be used to generate workflow skeletons
from templates by asking the user questions. It is easier to use
pegasus-init than to start a new workflow from scratch.

The mpi workflow looks like this:

![DAX](https://raw.githubusercontent.com/pegasus-isi/tutorials/master/BLUEWATERS/figures/tutorial-mpi-wf.jpg)

You will need to compile the mpi hello world executable. You will find a
Makefile in the examples directory
```
 $ cd mpi
 $ make install
 cc  -O pegasus-mpi-hw.c -c -o pegasus-mpi-hw.o
 cc  pegasus-mpi-hw.o -o pegasus-mpi-hw 

$ ls -lht bin/
$ ls -lht bin/
total 4.1M
-rwxr-xr-x 1 instr006 TRAIN_bafu 8.8M Aug  5 15:55 pegasus-mpi-hw
-rwxr-xr-x 1 instr006 TRAIN_bafu  223 Aug  5 15:47 mpi-hello-world-wrapper

```

You will find the c executable in it and a simple wrapper that launches
the executable using aprun.

```
$ cat bin/mpi-hello-world-wrapper 
  #!/bin/bash
  
  # before launching the job switch to the directory that
  # pegasus created for the workflow
  cd $PEGASUS_SCRATCH_DIR
  aprun -n $PEGASUS_CORES /mnt/a/u/training/instr006/pegasus-tutorial/mpi/bin/pegasus-mpi-hw "$@"

```

### Defining a workflow

Pegasus reads workflow descriptions from DAX files. The term “DAX”
is short for “Directed Acyclic Graph in XML”. DAX is an XML file
format that has syntax for expressing jobs, arguments, files, and
dependencies. In order to create a DAX it is necessary to write code
for a DAX generator. Pegasus comes with Perl, Java, and Python APIs for
writing DAX generators. In this tutorial we will show how to use the
Python API. [https://pegasus.isi.edu/documentation/python/](https://pegasus.isi.edu/documentation/python/)
The DAX for the split workflow can be generated by running the
generate_dax.sh script from the split directory, like this:

```
$ ./generate_dax.sh mpi.dax
Generated dax mpi.dax
``` 

This script will run a small Python program (daxgen.py) that generates a
file with a .dax extension using the Pegasus Python API.
Pegasus reads the DAX and generates an executable HTCondor workflow that
is run on an execution site.

# Planning a workflow

The pegasus-plan command is used to submit the workflow through Pegasus.
The pegasus-plan command reads the input workflow (DAX file specified
by --dax option), maps the abstract DAX to one or more execution sites,
and submits the generated executable workflow to HTCondor. Among other
things, the options to pegasus-plan tell Pegasus

 * the workflow to run
 * where (what site) to run the workflow
 * the input directory where the inputs are placed
 * the output directory where the outputs are placed

By default, the workflow is setup to run on the compute sites (i.e
sites with handle other than "local") defined in the sites.xml file. In
our example, the workflow will run on a site named **bluewaters** in the
sites.xml file.

To plan the split workflow invoke the pegasus-plan command using the
plan_dax.sh wrapper script as follows:

```
$  ./plan_dax.sh mpi.dax 
  2016.08.05 18:15:18.896 CDT:    
  2016.08.05 18:15:18.902 CDT:   ----------------------------------------------------------------------- 
  2016.08.05 18:15:18.908 CDT:   File for submitting this DAG to HTCondor           : mpi-hello-world-0.dag.condor.sub 
  2016.08.05 18:15:18.913 CDT:   Log of DAGMan debugging messages                 : mpi-hello-world-0.dag.dagman.out 
  2016.08.05 18:15:18.918 CDT:   Log of HTCondor library output                     : mpi-hello-world-0.dag.lib.out 
  2016.08.05 18:15:18.924 CDT:   Log of HTCondor library error messages             : mpi-hello-world-0.dag.lib.err 
  2016.08.05 18:15:18.929 CDT:   Log of the life of condor_dagman itself          : mpi-hello-world-0.dag.dagman.log 
  2016.08.05 18:15:18.934 CDT:    
  2016.08.05 18:15:18.939 CDT:   -no_submit given, not submitting DAG to HTCondor.  You can do this with: 
  2016.08.05 18:15:18.950 CDT:   ----------------------------------------------------------------------- 
  2016.08.05 18:15:32.619 CDT:   Your database is compatible with Pegasus version: 4.6.2 
  2016.08.05 18:15:33.429 CDT:   Submitting to condor mpi-hello-world-0.dag.condor.sub 
  2016.08.05 18:15:35.965 CDT:   Submitting job(s). 
  2016.08.05 18:15:35.971 CDT:   1 job(s) submitted to cluster 173. 
  2016.08.05 18:15:35.976 CDT:    
  2016.08.05 18:15:35.982 CDT:   Your workflow has been started and is running in the base directory: 
  2016.08.05 18:15:35.987 CDT:    
  2016.08.05 18:15:35.992 CDT:     /u/training/instr006/pegasus-tutorial/mpi/submit/instr006/pegasus/mpi-hello-world/run0002 
  2016.08.05 18:15:35.998 CDT:    
  2016.08.05 18:15:36.003 CDT:   *** To monitor the workflow you can run *** 
  2016.08.05 18:15:36.008 CDT:    
  2016.08.05 18:15:36.014 CDT:     pegasus-status -l /u/training/instr006/pegasus-tutorial/mpi/submit/instr006/pegasus/mpi-hello-world/run0002 
  2016.08.05 18:15:36.019 CDT:    
  2016.08.05 18:15:36.024 CDT:   *** To remove your workflow run *** 
  2016.08.05 18:15:36.030 CDT:    
  2016.08.05 18:15:36.035 CDT:     pegasus-remove /u/training/instr006/pegasus-tutorial/mpi/submit/instr006/pegasus/mpi-hello-world/run0002 
  2016.08.05 18:15:36.040 CDT:    
  2016.08.05 18:15:37.190 CDT:   Time taken to execute is 26.457 seconds  

```

The line in the output that starts with pegasus-status, contains the
command you can use to monitor the status of the workflow. The path it
contains is the path to the submit directory where all of the files
required to submit and monitor the workflow are stored.

This is what the mpi workflow looks like after Pegasus has finished planning the DAX:

![DAG](https://raw.githubusercontent.com/pegasus-isi/tutorials/master/BLUEWATERS/figures/tutorial-mpi-dag.jpg)

For this workflow the only jobs Pegasus needs to add are a directory
creation job, a stage-in job (for pegasus.html), and stage-out jobs
(for mpi job outputs). The cleanup jobs remove data that is no longer
required as workflow executes.


# Monitoring, statistics and debugging

All the command line interfaces described so far, and the ones
following, all have man pages which describe the command and arguments.
If you prefer a web version, they can be found at
 [https://pegasus.isi.edu/documentation/cli.php](https://pegasus.isi.edu/documentation/latest/cli.php)

After the workflow has been submitted you can monitor it using the *pegasus-status* command:

```bash
$ pegasus-status -l /YOUR/WF/PATH
  STAT  IN_STATE  JOB                                                                                                                  
  Run      04:33  mpi-hello-world-0 ( ./submit/instr006/pegasus/mpi-hello-world/run0002 )
  Idle     03:44   ┗━mpihw_ID0000001                                                                                                   
  Summary: 2 Condor jobs total (I:1 R:1)
  
  UNRDY READY   PRE  IN_Q  POST  DONE  FAIL %DONE STATE   DAGNAME                                 
      3     0     0     1     0     2     0  33.3 Running *mpi-hello-world-0.dag          
```

This command shows the workflow the current jobs (in the above output it
shows the *mpi* job in the idle state). It also gives statistics on
the number of jobs in each state and the percentage of the jobs in the
workflow that have finished successfully.

The workflow might sit in this state while jobs gets scheduled onto a node
via PBS on Bluewaters. The PBS scheduling interval on Bluewaters is in 
the range of 5-10 minutes.

Eventually the workflow will finish and pegasus-status will indicate success
```bash
$ pegasus-status -l /YOUR/WF/PATH

(no matching jobs found in Condor Q)
UNRDY READY   PRE  IN_Q  POST  DONE  FAIL %DONE STATE   DAGNAME                                 
    0     0     0     0     0     6     0 100.0 Success *mpi-hello-world-0.dag                  
Summary: 1 DAG total (Success:1)

```

In the case that one or more jobs fails, then the output of the
*pegasus-status* command above will have a non-zero value in the
*FAILURE* column.

You can debug the failure using the *pegasus-analyzer* command. This
command will identify the jobs that failed and show their output.
Because the workflow succeeded, *pegasus-analyzer* will only show some
basic statistics about the number of successful jobs:

```bash
$ pegasus-analyzer /YOUR/WF/PATH

************************************Summary*************************************

 Submit Directory   : /YOUR/WF/PATH
 Total jobs         :     14 (100.00%)
 # jobs succeeded   :     14 (100.00%)
 # jobs failed      :      0 (0.00%)
 # jobs unsubmitted :      0 (0.00%)
```

The *pegasus-statistics* command can be used to gather statistics about
the runtime of the workflow and its jobs. The *-s all* argument tells
the program to generate all statistics it knows how to calculate:

```bash
$ pegasus-statistics -s all /YOUR/WF/DIR

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
Tasks          1         0       0           1         0         1            
Jobs           6         0       0           6         0         6            
Sub-Workflows  0         0       0           0         0         0            
------------------------------------------------------------------------------

Workflow wall time                                       : 14 mins, 6 secs
Cumulative job wall time                                 : 12 secs
Cumulative job wall time as seen from submit side        : 22 secs
Cumulative job badput wall time                          : 
Cumulative job badput wall time as seen from submit side : 

Summary                       : ./statistics/summary.txt
Workflow execution statistics : ./statistics/workflow.txt
Job instance statistics       : ./statistics/jobs.txt
Transformation statistics     : ./statistics/breakdown.txt
Time statistics               : ./statistics/time.txt
```

The output of *pegasus-statistics* contains many definitions to help
users understand what all of the values reported mean. Among these are
the total wall time of the workflow, which is the time from when the
workflow was submitted until it finished, and the total cumulative job
wall time, which is the sum of the runtimes of all the jobs.

The *pegasus-statistics* command also writes out several reports in the
*statistics* subdirectory of the workflow submit directory:

```
$ ls statistics/
breakdown.txt
jobs.txt
summary.txt
time.txt
workflow.txt
```

The file *breakdown.txt*, for example, has min, max, and mean runtimes for each transformation:

```
$ cat statistics/breakdown.txt 


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

# b71f12b4-04a4-4cc3-a769-78ffcc175e51 (mpi-hello-world)
Transformation           Count     Succeeded Failed  Min       Max       Mean           Total     
dagman::post             6         6         0       0.0       1.0       0.667          4.0       
pegasus::cleanup         1         1         0       3.455     3.455     3.455          3.455     
pegasus::dirmanager      1         1         0       2.255     2.255     2.255          2.255     
pegasus::mpihw           1         1         0       0.0       0.0       0.0            0.0       
pegasus::rc-client       1         1         0       1.912     1.912     1.912          1.912     
pegasus::transfer        2         2         0       2.243     2.678     2.46           4.921     


# All (All)
Transformation           Count     Succeeded Failed  Min       Max       Mean           Total     
dagman::post             6         6         0       0.0       1.0       0.667          4.0       
pegasus::cleanup         1         1         0       3.455     3.455     3.455          3.455     
pegasus::dirmanager      1         1         0       2.255     2.255     2.255          2.255     
pegasus::mpihw           1         1         0       0.0       0.0       0.0            0.0       
pegasus::rc-client       1         1         0       1.912     1.912     1.912          1.912     
pegasus::transfer        2         2         0       2.243     2.678     2.46           4.921     


```

## Recovery from Failures

Executing workflows in a distributed environment can lead to failures. Often, 
they are a result of the underlying infrastructure being temporarily unavailable, 
or errors in workflow setup such as incorrect executables specified, or input files being unavailable.

In case of transient infrastructure failures such as a node being 
temporarily down in a cluster, Pegasus will automatically retry jobs 
in case of failure. After a set number of retries (usually once), 
a hard failure occurs, because of which workflow will eventually fail.

In most of the cases, these errors are correctable 
(either the resource comes back online or application errors are fixed). 
Once the errors are fixed, you may not want to start a new workflow but 
instead start from the point of failure. In order to do this, you can 
submit the rescue workflows automatically created in case of failures. 
A rescue workflow contains only a description of only the work that remains 
to be done.

### Submitting Rescue Workflows

In this example, we will take our previously run workflow and introduce errors such that workflow we just executed fails at runtime.

First we will "hide" the input file to cause a failure by renaming it:

```bash
$ cd ~/pegasus-tutorial/mpi
$ mv input/f.in input/f.in.bak
```

Now submit the workflow again:
```bash
$  ./plan_dax.sh mpi.dax 
   2016.08.05 19:46:26.552 CDT:    
   2016.08.05 19:46:26.558 CDT:   ----------------------------------------------------------------------- 
   2016.08.05 19:46:26.563 CDT:   File for submitting this DAG to HTCondor           : mpi-hello-world-0.dag.condor.sub 
   2016.08.05 19:46:26.568 CDT:   Log of DAGMan debugging messages                 : mpi-hello-world-0.dag.dagman.out 
   2016.08.05 19:46:26.574 CDT:   Log of HTCondor library output                     : mpi-hello-world-0.dag.lib.out 
   2016.08.05 19:46:26.579 CDT:   Log of HTCondor library error messages             : mpi-hello-world-0.dag.lib.err 
   2016.08.05 19:46:26.584 CDT:   Log of the life of condor_dagman itself          : mpi-hello-world-0.dag.dagman.log 
   2016.08.05 19:46:26.590 CDT:    
   2016.08.05 19:46:26.595 CDT:   -no_submit given, not submitting DAG to HTCondor.  You can do this with: 
   2016.08.05 19:46:26.605 CDT:   ----------------------------------------------------------------------- 
   2016.08.05 19:46:31.743 CDT:   Your database is compatible with Pegasus version: 4.6.2 
   2016.08.05 19:46:31.900 CDT:   Submitting to condor mpi-hello-world-0.dag.condor.sub 
   2016.08.05 19:46:33.117 CDT:   Submitting job(s). 
   2016.08.05 19:46:33.122 CDT:   1 job(s) submitted to cluster 184. 
   2016.08.05 19:46:33.156 CDT:    
   2016.08.05 19:46:33.161 CDT:   Your workflow has been started and is running in the base directory: 
   2016.08.05 19:46:33.167 CDT:    
   2016.08.05 19:46:33.172 CDT:     /u/training/instr006/pegasus-tutorial/mpi/submit/instr006/pegasus/mpi-hello-world/run0002 
   2016.08.05 19:46:33.177 CDT:    
   2016.08.05 19:46:33.183 CDT:   *** To monitor the workflow you can run *** 
   2016.08.05 19:46:33.188 CDT:    
   2016.08.05 19:46:33.193 CDT:     pegasus-status -l /u/training/instr006/pegasus-tutorial/mpi/submit/instr006/pegasus/mpi-hello-world/run0002 
   2016.08.05 19:46:33.199 CDT:    
   2016.08.05 19:46:33.204 CDT:   *** To remove your workflow run *** 
   2016.08.05 19:46:33.209 CDT:    
   2016.08.05 19:46:33.215 CDT:     pegasus-remove /u/training/instr006/pegasus-tutorial/mpi/submit/instr006/pegasus/mpi-hello-world/run0002 
   2016.08.05 19:46:33.220 CDT:    
   2016.08.05 19:46:33.396 CDT:   Time taken to execute is 9.811 seconds 

```

We will add -w option to pegasus-status to watch automatically till the workflow finishes:

```bash
 $ pegasus-status /YOUR/WF/PATH
(no matching jobs found in Condor Q)
UNREADY   READY     PRE  QUEUED    POST SUCCESS FAILURE %DONE
      4       0       0       0       0       1       1  16.7

```

Now we can use the pegasus-analyzer command to determine what went wrong:
```bash
$ pegasus-analyzer /YOUR/WF/PATH
************************************Summary*************************************

 Submit Directory   : submit/instr006/pegasus/mpi-hello-world/run0002
 Total jobs         :      6 (100.00%)
 # jobs succeeded   :      1 (16.67%)
 # jobs failed      :      1 (16.67%)
 # jobs unsubmitted :      4 (66.67%)

******************************Failed jobs' details******************************

=========================stage_in_local_bluewaters_0_0==========================

 last state: POST_SCRIPT_FAILED
       site: local
submit file: stage_in_local_bluewaters_0_0.sub
output file: stage_in_local_bluewaters_0_0.out.001
 error file: stage_in_local_bluewaters_0_0.err.001

-------------------------------Task #1 - Summary--------------------------------

site        : local
hostname    : h2ologin2.ncsa.illinois.edu
executable  : /mnt/b/projects/eot/bafu/pegasus/pegasus-4.6.2dev/bin/pegasus-transfer
arguments   :   --threads   2  
exitcode    : 1
working dir : /mnt/a/u/training/instr006/pegasus-tutorial/mpi/submit/instr006/pegasus/mpi-hello-world/run0002

------------------Task #1 - pegasus::transfer - None - stdout-------------------

2016-08-05 19:50:22,153    INFO:  Reading URL pairs from stdin
2016-08-05 19:50:22,157    INFO:  1 transfers loaded
2016-08-05 19:50:22,157    INFO:  PATH=/usr/bin:/bin:/usr/local/globus-5.2.5/bin
2016-08-05 19:50:22,157    INFO:  LD_LIBRARY_PATH=/sw/xe/darshan/2.3.0/darshan-2.3.0_cle52/lib:/usr/local/globus-5.2.5/lib64:/usr/local/globus/lib64:/usr/local/globus-5.2.5/lib
2016-08-05 19:50:22,216    INFO:  --------------------------------------------------------------------------------
2016-08-05 19:50:22,216    INFO:  Starting transfers - attempt 1
2016-08-05 19:50:24,223    INFO:  /bin/cp -f -R -L '/mnt/a/u/training/instr006/pegasus-tutorial/mpi/input/f.in' '/mnt/a/u/training/instr006/pegasus-tutorial/mpi/bluewaters/scratch/instr006/pegasus/mpi-hello-world/run0002/f.in'
2016-08-05 19:50:24,240    INFO:  /bin/cp: cannot stat `/mnt/a/u/training/instr006/pegasus-tutorial/mpi/input/f.in': No such file or directory
2016-08-05 19:50:24,240   ERROR:  Command exited with non-zero exit code (1): /bin/cp ...
2016-08-05 19:50:59,272    INFO:  --------------------------------------------------------------------------------
2016-08-05 19:50:59,273    INFO:  Starting transfers - attempt 2
2016-08-05 19:51:01,425    INFO:  /bin/cp -f -R -L '/mnt/a/u/training/instr006/pegasus-tutorial/mpi/input/f.in' '/mnt/a/u/training/instr006/pegasus-tutorial/mpi/bluewaters/scratch/instr006/pegasus/mpi-hello-world/run0002/f.in'
2016-08-05 19:51:03,694    INFO:  /bin/cp: cannot stat `/mnt/a/u/training/instr006/pegasus-tutorial/mpi/input/f.in': No such file or directory
2016-08-05 19:51:03,694   ERROR:  Command exited with non-zero exit code (1): /bin/cp ...
2016-08-05 19:53:17,795    INFO:  --------------------------------------------------------------------------------
2016-08-05 19:53:17,796    INFO:  Starting transfers - attempt 3
2016-08-05 19:53:19,801    INFO:  /bin/cp -f -R -L '/mnt/a/u/training/instr006/pegasus-tutorial/mpi/input/f.in' '/mnt/a/u/training/instr006/pegasus-tutorial/mpi/bluewaters/scratch/instr006/pegasus/mpi-hello-world/run0002/f.in'
2016-08-05 19:53:19,818    INFO:  /bin/cp: cannot stat `/mnt/a/u/training/instr006/pegasus-tutorial/mpi/input/f.in': No such file or directory
2016-08-05 19:53:19,818   ERROR:  Command exited with non-zero exit code (1): /bin/cp ...
2016-08-05 19:53:19,818    INFO:  --------------------------------------------------------------------------------
2016-08-05 19:53:19,819    INFO:  Stats: Total 3 transfers, 0.0 B transferred in 178 seconds. Rate: 0.0 B/s (0.0 b/s)
2016-08-05 19:53:19,819    INFO:         Between sites bluewaters->bluewaters : 3 transfers, 0.0 B transferred in 178 seconds. Rate: 0.0 B/s (0.0 b/s)
2016-08-05 19:53:19,819 CRITICAL:  Some transfers failed! See above, and possibly stderr.

```

The above listing indicates that it could not transfer f.in 
Let's correct that error by restoring the f.in file:

```bash
$  mv input/f.in.bak input/f.in
```

Now in order to start the workflow from where we left off, instead of executing pegasus-plan 
we will use the command pegasus-run on the directory from our previous failed workflow run:

```bash
$ pegasus-run  /YOUR/WF/PATH
Rescued /u/training/instr006/pegasus-tutorial/mpi/submit/instr006/pegasus/mpi-hello-world/run0002/mpi-hello-world-0.log as /u/training/instr006/pegasus-tutorial/mpi/submit/instr006/pegasus/mpi-hello-world/run0002/mpi-hello-world-0.log.000
Submitting to condor mpi-hello-world-0.dag.condor.sub
Submitting job(s).
1 job(s) submitted to cluster 191.

Your workflow has been started and is running in the base directory:

  submit/instr006/pegasus/mpi-hello-world/run0002

*** To monitor the workflow you can run ***

  pegasus-status -l submit/instr006/pegasus/mpi-hello-world/run0002

*** To remove your workflow run ***

  pegasus-remove submit/instr006/pegasus/mpi-hello-world/run0002

```

The workflow will now run to completion and succeed.
```bash
pegasus-status -l -w /YOUR/WF/PATH

(no matching jobs found in Condor Q)
UNRDY READY   PRE  IN_Q  POST  DONE  FAIL %DONE STATE   DAGNAME                                 
    0     0     0     0     0     6     0 100.0 Success *mpi-hello-world-0.dag                  
Summary: 1 DAG total (Success:1)

```
# Catalogs

There are three information catalogs that Pegasus uses when planning
the workflow. These are the *Site* catalog, *Transformation* catalog, and
*Replica* catalog.


![Catalogs Used by Pegasus](https://raw.githubusercontent.com/pegasus-isi/tutorials/master/BLUEWATERS/figures/tutorial-pegasus-catalogs.png)

## Site Catalog

The site catalog describes the sites where the workflow jobs are to
be executed. Typically the sites in the site catalog describe remote
clusters, such as Slurm clusters or HTCondor pools. The site catalog
in this tutorial is generated by *pegasus-init* and looks something
like:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<sitecatalog xmlns="http://pegasus.isi.edu/schema/sitecatalog" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://pegasus.isi.edu/schema/sitecatalog http://pegasus.isi.edu/schema/sc-4.1.xsd" version="4.1">

    <!-- The local site contains information about the submit host -->
    <site handle="local">
        <!-- This is where intermediate data will be stored -->
        <directory type="shared-scratch" path="/mnt/a/u/training/instr006/pegasus-tutorial/mpi/scratch">
            <file-server operation="all" url="file:///mnt/a/u/training/instr006/pegasus-tutorial/mpi/scratch"/>
        </directory>
        <!-- This is where output data will be stored -->
        <directory type="shared-storage" path="/mnt/a/u/training/instr006/pegasus-tutorial/mpi/output">
            <file-server operation="all" url="file:///mnt/a/u/training/instr006/pegasus-tutorial/mpi/output"/>
        </directory>
	<profile namespace="env" key="JAVA_HOME">${JAVA_HOME}</profile>
    </site>

    <site handle="bluewaters" arch="x86_64" os="LINUX">
         <!-- Scratch directory on the cluster -->
        <directory type="shared-scratch" path="/mnt/a/u/training/instr006/pegasus-tutorial/mpi/bluewaters/scratch">
            <file-server operation="all" url="file:///mnt/a/u/training/instr006/pegasus-tutorial/mpi/bluewaters/scratch"/>
        </directory>

        <profile namespace="pegasus" key="style">glite</profile>

        <!-- This tells glite what scheduler to use. It can be 'pbs' or 'sge' -->
        <profile namespace="condor" key="grid_resource">pbs</profile>

        <!-- This tells glite what batch queue to submit jobs to -->
        <profile namespace="pegasus" key="queue">normal</profile>
	
	    <!--- This tells pegasus to have the auxillary jobs run on submit host 
	      and not go through the local PBS queue -->
	    <profile namespace="pegasus" key="auxillary.local">true</profile>

	    <!-- This profile tells Pegasus where the worker package is installed on the site -->
        <!-- Without this, Pegasus will automatically stage a worker package to the site -->
        <profile namespace="env"  key="PEGASUS_HOME">/mnt/a/u/training/instr006/SOFTWARE/install/pegasus/default</profile> 

        <!-- This profile tells Pegasus to create two clustered jobs
            per level of the workflow, when horizontal clustering is
            enabled -->
	    <profile namespace="pegasus" key="clusters.num" >2</profile>

    </site>

</sitecatalog>

```

Pegasus supports many different file transfer protocols. For example,
you can set up transfers from your submit host to the cluster using SCP.
In that case, the scratch file system with have a *scp://* URL. To specify
the passwordless ssh key to use, you will need to add a pegasus profile
key named SSH_PRIVATE_KEY that tells Pegasus where to find the private
key to use for SCP transfers. Remember to add the passwordless key to
your ssh authorized keys.
 [https://pegasus.isi.edu/documentation/transfer.php](https://pegasus.isi.edu/documentation/transfer.php)


## Transformation Catalog

The transformation catalog describes all of the executables (called
*transformations*) used by the workflow. This description includes
the site(s) where they are located, the architecture and operating
system they are compiled for, and any other information required to
properly transfer them to the execution site and run them.

For this tutorial, the transformation catalog is in the file
*tc.txt*:

```
$ cat tc.txt

# This is the transformation catalog. It lists information about each of the
# executables that are used by the workflow.


# For submitting MPI jobs directly through condor 
# we need a wrapper that does the aprun on the executable


tr pegasus::mpihw {
    site bluewaters {
        pfn "/mnt/a/u/training/instr006/pegasus-tutorial/mpi/bin/mpi-hello-world-wrapper"
        arch "x86_64"
        os "LINUX"
        type "INSTALLED"
    }
}
```

A thing to note is that we are pointing to the wrapper instead of the mpi 
executatble. This is required as on Bluewaters all mpi jobs should go through
aprun.

## Replica Catalog

The final catalog is the *Replica* catalog. This catalog tells Pegasus
where to find each of the input files for the workflow.

The example that you ran, was configured with the inputs already present 
on the submit host (where Pegasus is installed) in a directory. 
If you have inputs at external servers, then you can specify the URLs 
to the input files in the Replica Catalog. 

All files in a Pegasus workflow are referred to in the DAX using their
Logical File Name (LFN). These LFNs are mapped to Physical File Names
(PFNs) when Pegasus plans the workflow. This level of indirection
enables Pegasus to map abstract DAXes to different execution sites and
plan out the required file transfers automatically.

The Replica Catalog for the diamond workflow is in the rc.txt file:
```bash
more rc.txt
# This is the replica catalog. It lists information about each of the
# input files used by the workflow. 
# You can use this to specify locations to input files present on external servers.

# The format is:
# LFN     PFN    pool="SITE"
#
# For example:
#data.txt  file:///tmp/data.txt         site="local"
#data.txt  http://example.org/data.txt  site="example"
f.in file:///u/training/traXXX/pegasus-tutorial/mpi/input/f.in   site="local"
```

**Note** By default (unless specified in properties), Pegasus picks ups the 
replica catalog from a text file named rc.txt in the current working 
directory from where pegasus-plan is invoked. In our tutorial, input files 
are on the submit host and we used the --input dir option to pegasus-plan
to specify where they are located.

# Specifying Task Requirements for MPI jobs

To specify the task requirements for MPI jobs, you can asscociate pegasus
task requirement profiles with the job. You can do this either in the 
transformation catalog or in the DAX itself. For this tutorial, we have
specified the requirements as pegasus profiles with the mpi job.

```xml
<?xml version="1.0" encoding="UTF-8"?>
<!-- generated: 2016-08-05 15:57:44.421208 -->
<!-- generated by: instr006 -->
<!-- generator: python -->
<adag xmlns="http://pegasus.isi.edu/schema/DAX" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
xsi:schemaLocation="http://pegasus.isi.edu/schema/DAX http://pegasus.isi.edu/schema/dax-
3.6.xsd" version="3.6" name="mpi-hello-world">
	<metadata key="created">Fri Aug  5 15:57:44 2016</metadata>
	<metadata key="creator">instr006@h2ologin1</metadata>
	
	
	<job id="ID0000001" namespace="pegasus" name="mpihw">
		<argument>-i  <file name="f.in"/> -o  <file name="f.out"/></argument>
		<profile namespace="globus" key="jobtype">mpi</profile>
		
		<!-- the number of nodes required -->
		<profile namespace="pegasus" key="nodes">2</profile>
		
		<!-- the number of cores required -->
		<profile namespace="pegasus" key="cores">32</profile>
		
		<!-- specifies the number of processors per node that the job should use -->
		<profile namespace="pegasus" key="ppn">16</profile>  
		
		<!-- expected runtime of job in seconds -->
		<profile namespace="pegasus" key="runtime">300</profile>
		<uses name="f.in" link="input"/>
		<uses name="f.out" link="output"/>
	</job>
</adag>

```



The task requirement profiles and the mappings to the PBS parameters are explained 
[here] (https://pegasus.isi.edu/documentation/glite.php#glite_mappings)

# Conclusion
This brings you to the end of the Pegasus tutorial on Bluewaters. 
The tutorial should have given you an overview of how to compose a simple
workflow using Pegasus and running it on Bluewaters.
The tutorial examples, should provide a good starting point for you to 
port your application to a Pegasus workflow. If you need help in porting your application to Pegasus contact us on the following support channels

public mailman list : <pegasus-users@isi.edu>

private support list: <pegasus-support@isi.edu>

Detailed Pegasus Documentation can be found 
[here] (https://pegasus.isi.edu/documentation/).
