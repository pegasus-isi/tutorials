
This tutorial will take you through the steps of creating and
running a simple scientific workflow using Pegasus Workflow
Management System (WMS) on XSEDE. This tutorial is intended
for new users who want to get a quick overview of Pegasus
concepts and usage. More information about the topics covered
in this tutorial can be found in the Pegasus user guide at
 [https://pegasus.isi.edu/documentation/](https://pegasus.isi.edu/documentation/)

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

## Bosco: a pilot system for HTCondor

The remote jobs submission approach for when using Pegasus on XSEDE
depends on what type of workload you intend to run. If you have a small
number of traditional MPI jobs, the standard Globus GRAM servies is a
good solution. However, if your workload is more of a high throughput
workload, such as a large number of tasks, Bosco is a good solution.
Note that the tasks can have varying core/memory requirements, as long
as task is not larger than a single node on the target cluster.

[Bosco](http://bosco.opensciencegrid.org/) is a pilot system for
HTCondor. Pilots under HTCondor is generally called *glideins* as the
approach is to glide in the execution part of HTCondor to a cluster
and thus extend your local HTCondor pool onto that cluster. Once you
have glideins, you can use regular HTCondor jobs, and commands such as
*condor_status* to view the status of your pool.

To get started with Bosco, first log in to workflow.iu.xsede.org, and then
run:

```
$ /opt/pegasus/bosco-installer/bosco_install
```

This will install Bosco into your home directory. It is important to
understand that his is a local install available to your user only. For
example, when listing the jobs in the queue, you will only see your own
jobs. Because the setup is local, you also have to source a shell setup
script to get the correct environment. **Note: you will have to source
this script every time you log in.**

```
$ source $HOME/bosco/bosco_setenv
```

Go ahead and start the system. You only need to do this once (or again
if the submit host gets rebooted):

```
$ bosco_start
```

After a few seconds we can verify that the system is running with
the *condor_q* and *condor_status* commands. The latter will return
an empty list as we do not have any execution resources added yet.

```
$ condor_q

-- Schedd: workflow.iu.xsede.org : 127.0.0.1:11000?...
 ID      OWNER            SUBMITTED     RUN_TIME ST PRI SIZE CMD

0 jobs; 0 completed, 0 removed, 0 idle, 0 running, 0 held, 0 suspended
$ condor_status
```

Let's tell Bosco about our SDSC Comet account. Your SDSC username might
be different from your XSEDE portal username. If you are using a XSEDE
training account, the SDSC account is xdtr*NN* where the NN is the same
as the number from the train*NN* account. If you are using your own
XSEDE account, you can find the username on the
[XSEDE accounts page](https://www.xsede.org/group/xup/accounts).

```
$ bosco_cluster -a YOUR_SDSC_USERNAME@comet-ln2.sdsc.edu pbs
```

Bosco needs a little bit more information to be able to submit
the glideins to Comet. Log in to your Comet account via ssh
(**note: this step has to take place on Comet**) and create the
*~/bosco/glite/bin/pbs_local_submit_attributes.sh* file with the following
content. You can find your allocation by running *show_accounts* and
looking at the project column.

```
echo "#PBS -q compute"
echo "#PBS -l nodes=1:ppn=24"
echo "#PBS -l walltime=24:00:00"
echo "#PBS -A [YOUR_COMET_ALLOCATION]"
``` 

Also chmod the file:

```
$ chmod 755 ~/bosco/glite/bin/pbs_local_submit_attributes.sh
``` 

You should now be all set up to run jobs on Comet.


## Example 1: Split workflow, running on top of Bosco

For this tutorial we will be using the an example split workflow,
which can be created like this:

```
$ pegasus-init split
Do you want to generate a tutorial workflow? (y/n) [n]: y
1: Local Machine
2: USC HPCC Cluster
3: OSG from ISI submit node
4: XSEDE, with Bosco
What environment is tutorial to be setup for? (1-4) [1]: 4
1: Process
2: Pipeline
3: Split
4: Merge
5: Diamond
What tutorial workflow do you want? (1-5) [1]: 3
```

Tip: The pegasus-init tool can be used to generate workflow skeletons
from templates by asking the user questions. It is easier to use
pegasus-init than to start a new workflow from scratch.

The split workflow looks like this:

![DAX](https://raw.githubusercontent.com/pegasus-isi/tutorials/master/XSEDE/figures/tutorial-split-wf.jpg)

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
$ ./generate_dax.sh split.dax
Generated dax split.dax
``` 

This script will run a small Python program (daxgen.py) that generates a
file with a .dax extension using the Pegasus Python API.
Pegasus reads the DAX and generates an executable HTCondor workflow that
is run on an execution site.

### Catalogs

There are three information catalogs that Pegasus uses when planning
the workflow. These are the *Site* catalog, *Transformation* catalog, and
*Replica* catalog.

#### Site Catalog

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
        <directory type="shared-scratch" path="/home/user/split/scratch">
            <file-server operation="all" url="file:///home/user/split/scratch"/>
        </directory>
        <!-- This is where output data will be stored -->
        <directory type="shared-storage" path="/home/user/split/output">
            <file-server operation="all" url="file:///home/user/split/output"/>
        </directory>
    </site>

    <site handle="condorpool" arch="x86_64" os="LINUX">
        <!-- These profiles tell Pegasus that the site is a plain Condor pool -->
        <profile namespace="pegasus" key="style">condor</profile>
        <profile namespace="condor" key="universe">vanilla</profile>

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


#### Transformation Catalog

The transformation catalog describes all of the executables (called
*transformations*) used by the workflow. This description includes
the site(s) where they are located, the architecture and operating
system they are compiled for, and any other information required to
properly transfer them to the execution site and run them.

For this tutorial, the transformation catalog is in the file
*tc.txt*:

```
tr wc {
    site condorpool {
        pfn "/usr/bin/wc"
        arch "x86_64"
        os "LINUX"
        type "INSTALLED"
    }
}

tr split {
    site condorpool {
        pfn "/usr/bin/split"
        arch "x86_64"
        os "LINUX"
        type "INSTALLED"
    }
}
```

#### Replica Catalog

The final catalog is the *Replica* catalog. This catalog tells Pegasus
where to find each of the input files for the workflow.

All files in a Pegasus workflow are referred to in the DAX using their
Logical File Name (LFN). These LFNs are mapped to Physical File Names
(PFNs) when Pegasus plans the workflow. This level of indirection
enables Pegasus to map abstract DAXes to different execution sites and
plan out the required file transfers automatically.


### Planning a workflow

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
our example, the workflow will run on a site named "condorpool" in the
sites.xml file.

To plan the split workflow invoke the pegasus-plan command using the
plan_dax.sh wrapper script as follows:

```
$ ./plan_dax.sh split.dax 
2016.07.14 14:38:28.283 EDT:    
2016.07.14 14:38:28.290 EDT:   ----------------------------------------------------------------------- 
2016.07.14 14:38:28.296 EDT:   File for submitting this DAG to HTCondor           : split-0.dag.condor.sub 
2016.07.14 14:38:28.301 EDT:   Log of DAGMan debugging messages                 : split-0.dag.dagman.out 
2016.07.14 14:38:28.307 EDT:   Log of HTCondor library output                     : split-0.dag.lib.out 
2016.07.14 14:38:28.313 EDT:   Log of HTCondor library error messages             : split-0.dag.lib.err 
2016.07.14 14:38:28.323 EDT:   Log of the life of condor_dagman itself          : split-0.dag.dagman.log 
2016.07.14 14:38:28.328 EDT:    
2016.07.14 14:38:28.345 EDT:   ----------------------------------------------------------------------- 
2016.07.14 14:38:29.527 EDT:   Submitting to condor split-0.dag.condor.sub 
2016.07.14 14:38:29.613 EDT:   Submitting job(s). 
2016.07.14 14:38:29.618 EDT:   1 job(s) submitted to cluster 31. 
2016.07.14 14:38:29.624 EDT:    
2016.07.14 14:38:29.631 EDT:   Your workflow has been started and is running in the base directory: 
2016.07.14 14:38:29.637 EDT:    
2016.07.14 14:38:29.643 EDT:     /home/user/split/submit/user/pegasus/split/run0001 
2016.07.14 14:38:29.648 EDT:    
2016.07.14 14:38:29.654 EDT:   *** To monitor the workflow you can run *** 
2016.07.14 14:38:29.659 EDT:    
2016.07.14 14:38:29.665 EDT:     pegasus-status -l /home/user/split/submit/user/pegasus/split/run0001 
2016.07.14 14:38:29.671 EDT:    
2016.07.14 14:38:29.677 EDT:   *** To remove your workflow run *** 
2016.07.14 14:38:29.682 EDT:    
2016.07.14 14:38:29.689 EDT:     pegasus-remove /home/user/split/submit/user/pegasus/split/run0001 
2016.07.14 14:38:29.695 EDT:    
2016.07.14 14:38:29.907 EDT:   Time taken to execute is 4.471 seconds 

```

The line in the output that starts with pegasus-status, contains the
command you can use to monitor the status of the workflow. The path it
contains is the path to the submit directory where all of the files
required to submit and monitor the workflow are stored.

This is what the split workflow looks like after Pegasus has finished planning the DAX:

![DAX](https://raw.githubusercontent.com/pegasus-isi/tutorials/master/XSEDE/figures/tutorial-split-dag.jpg)

For this workflow the only jobs Pegasus needs to add are a directory
creation job, a stage-in job (for pegasus.html), and stage-out jobs
(for wc count outputs). The cleanup jobs remove data that is no longer
required as workflow executes.


## Monitoring, statistics and debugging

All the command line interfaces described so far, and the ones
following, all have man pages which describe the command and arguments.
If you prefer a web version, they can be found at
 [https://pegasus.isi.edu/documentation/cli.php](https://pegasus.isi.edu/documentation/latest/cli.php)

After the workflow has been submitted you can monitor it using the *pegasus-status* command:

```
$ pegasus-status /YOUR/WF/PATH
STAT  IN_STATE  JOB
Run      02:04  two-xsede-sites-0 ( /YOUR/WF/PATH )
Idle     01:11   ┗━step1_ID0000001
Summary: 2 Condor jobs total (I:1 R:1), 1 Condor-G job (P:1)

UNREADY   READY     PRE  QUEUED    POST SUCCESS FAILURE %DONE
     10       0       0       1       0       3       0  21.4
Summary: 1 DAG total (Running:1)
```

This command shows the workflow the current jobs (in the above output it
shows the *step1* job in the idle state). It also gives statistics on
the number of jobs in each state and the percentage of the jobs in the
workflow that have finished successfully.

In the case that one or more jobs fails, then the output of the
*pegasus-status* command above will have a non-zero value in the
*FAILURE* column.

You can debug the failure using the *pegasus-analyzer* command. This
command will identify the jobs that failed and show their output.
Because the workflow succeeded, *pegasus-analyzer* will only show some
basic statistics about the number of successful jobs:

```
$ pegasus-analyzer /YOUR/WF/PATH

************************************Summary*************************************

 Submit Directory   : /YOUR/WF/PATH
 Total jobs         :     14 (100.00%)
 # jobs succeeded   :     14 (100.00%)
 # jobs failed      :      0 (0.00%)
 # jobs unsubmitted :      0 (0.00%)
```
If the workflow had failed you would see something like this:

```shell
$ pegasus-analyzer /YOUR/WF/PATH

**************************Summary*************************************

 Total jobs         :      7 (100.00%)
 # jobs succeeded   :      2 (28.57%)
 # jobs failed      :      1 (14.29%)
 # jobs unsubmitted :      4 (57.14%)

**********************Failed jobs' details****************************

====================step1_ID0000001==============================

 last state: POST_SCRIPT_FAILED
       site: sdsc-gordon
submit file: step1_ID0000001.sub
output file: step1_ID0000001.out.003
 error file: step1_ID0000001.err.003

-----------------------Task #1 - Summary-----------------------------

site        : sdsc-gordon
hostname    : local-01
executable  : /home/tutorial/bin/step1
arguments   : -i f.a -o f.b1 -o f.b2
exitcode    : -128
working dir : -

-------------Task #1 - step1 - ID0000001 - stderr---------------

FATAL: Executable not found.
```

In this example I removed the *step1* executable and
re-planned/re-submitted the workflow. The output of *pegasus-analyzer*
indicates that the preprocess task failed with an error message that
indicates that the executable could not be found.

The *pegasus-statistics* command can be used to gather statistics about
the runtime of the workflow and its jobs. The *-s all* argument tells
the program to generate all statistics it knows how to calculate:

```
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
#   The walltime from the start of the workflow execution to the end as
#   reported by the DAGMAN.In case of rescue dag the value is the
#   cumulative of all retries.
# Workflow cumulative job wall time:
#   The sum of the walltime of all jobs as reported by kickstart.
#   In case of job retries the value is the cumulative of all retries.
#   For workflows having sub workflow jobs (i.e SUBDAG and SUBDAX jobs),
#   the walltime value includes jobs from the sub workflows as well.
# Cumulative job walltime as seen from submit side:
#   The sum of the walltime of all jobs as reported by DAGMan.
#   This is similar to the regular cumulative job walltime, but includes
#   job management overhead and delays. In case of job retries the value
#   is the cumulative of all retries. For workflows having sub workflow
#   jobs (i.e SUBDAG and SUBDAX jobs), the walltime value includes jobs
#   from the sub workflows as well.
------------------------------------------------------------------------------
Type           Succeeded Failed  Incomplete  Total     Retries   Total+Retries
Tasks          2         0       0           2         0         2
Jobs           14        0       0           14        0         14
Sub-Workflows  0         0       0           0         0         0
------------------------------------------------------------------------------

Workflow wall time                               : 18 mins, 40 secs
Workflow cumulative job wall time                : 4 mins, 23 secs
Cumulative job walltime as seen from submit side : 6 mins, 6 secs

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

# e720e913-6635-4d6c-a5ae-41b33c899529 (two-xsede-sites)
Transformation           Count     Succeeded Failed  Min       Max       Mean      Total     
dagman::post             14        14        0       5.0       6.0       5.071     71.0      
pegasus::cleanup         6         6         0       5.088     21.541    8.312     49.872    
pegasus::dirmanager      2         2         0       5.438     5.471     5.454     10.909    
pegasus::transfer        4         4         0       3.446     37.37     20.594    82.378    
step1                    1         1         0       60.02     60.02     60.02     60.02     
step2                    1         1         0       60.047    60.047    60.047    60.047    


# All (All)
Transformation           Count     Succeeded  Failed  Min        Max        Mean      Total     
dagman::post             14        14         0       5.0        6.0        5.071     71.0      
pegasus::cleanup         6         6          0       5.088      21.541     8.312     49.872    
pegasus::dirmanager      2         2          0       5.438      5.471      5.454     10.909    
pegasus::transfer        4         4          0       3.446      37.37      20.594    82.378    
step1                    1         1          0       60.02      60.02      60.02     60.02     
step2                    1         1          0       60.047     60.047     60.047    60.047    
```

## <a name="TOC-Example-2:-Running-MPI-jobs"></a>Example 2: Running MPI jobs

You can also submit workflows that have MPI jobs to the XSEDE sites
using Pegasus. This example executes a workflow consisting of a single
MPI job on SDSC Gordon Cluster. The MPI executable is a simple hello
world MPI executable that is pre-installed on the Gordon cluster.


```shell
$ cd ~/pegasus-tutorial/mpi-example
$  ./dax-generator.py > mpi-hw.dax 
```

The generated DAX file will something like this

```xml
<?xml version="1.0" encoding="UTF-8"?>
<!-- generated: 2015-07-14 18:44:43.425799 -->
<!-- generated by: rynge -->
<!-- generator: python -->
<adag xmlns="http://pegasus.isi.edu/schema/DAX" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://pegasus.isi.edu/schema/DAX http://pegasus.isi.edu/schema/dax-3.5.xsd" version="3.5" name="mpi-example">
	<file name="input.data">
		<pfn url="file:///home/rynge/git/tutorials/XSEDE/mpi-example/input.data" site="local"/>
	</file>
	<job id="ID0000001" name="mpi-hello-world">
		<argument>-o <file name="output.data"/></argument>
		<uses name="input.data" link="input"/>
		<uses name="output.data" link="output" transfer="true"/>
	</job>
</adag>
```



### Catalogs Generation

The transformation catalog for this example is pre generated and looks
like this below

```
cat transformation.catalog

tr mpi-hello-world {
    site sdsc-gordon {
        # refer to the wrapper that calls mpirun_rsh
        pfn "/home/ux454281/software/mpi-hello-world/mpi-hello-world-wrapper"
        arch "x86_64"
        os "linux"
        type "INSTALLED"
        # job type set to single as globus job manager on gordon
        # is broken for MPI jobs
        profile globus "jobtype" "single"
        profile globus "maxwalltime" "10"
        profile globus "hostcount" "1"
        profile globus "count" "16"
    }
}
```

When running a MPI job on the cluster, usually you need to specify
extra parameters such as how many nodes a job runs on, how many cores
it requires, the walltime for the job. These parameters have been
specified in the transformation catalog as globus profiles
count|hostcount|maxwalltime. 


To generate the site catalog for this example, execute the script 

```shell
$ ./generate-site-catalog.sh 
```

The resulting file will look something like this

```xml
<?xml version="1.0" encoding="UTF-8"?>
<sitecatalog xmlns="http://pegasus.isi.edu/schema/sitecatalog" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://pegasus.isi.edu/s
chema/sitecatalog http://pegasus.isi.edu/schema/sc-4.0.xsd" version="4.0">

    <site  handle="local" arch="x86" os="LINUX">
        <!-- the base directory where workflow jobs will execute for local site -->
        <directory type="shared-scratch" path="/home/rynge/git/tutorials/XSEDE/mpi-example/work/scratch">
            <file-server operation="all" url="file:///home/rynge/git/tutorials/XSEDE/mpi-example/work/scratch"/>
        </directory>

        <!-- the directory where outputs will be placed  -->
        <directory type="local-storage" path="/home/rynge/git/tutorials/XSEDE/mpi-example/outputs">
            <file-server operation="all" url="file:///home/rynge/git/tutorials/XSEDE/mpi-example/outputs"/>
        </directory>
    </site>

    <site  handle="sdsc-gordon" arch="x86_64" os="LINUX">
        <grid  type="gt5" contact="gordon-ln4.sdsc.xsede.org:2119/jobmanager-fork" scheduler="Fork" jobtype="auxillary"/>
        <grid  type="gt5" contact="gordon-ln4.sdsc.xsede.org:2119/jobmanager-pbs" scheduler="unknown" jobtype="compute"/>

        <directory type="shared-scratch" path="/oasis/scratch/ux454281/temp_project">
            <file-server operation="all" url="gsiftp://oasis-dm.sdsc.xsede.org:2811/oasis/scratch/ux454281/temp_project"/>
        </directory>

        <profile namespace="globus" key="project">TG-STA110014S</profile>
        <profile namespace="env" key="PEGASUS_HOME">/home/ux454281/software/pegasus/pegasus-4.5.0</profile>
	
    </site>

</sitecatalog>
```

### Plan and submit the workflow

We will now plan this workflow through Pegasus and generate a HTCondor
DAGMan workflow that will be submitted to the local HTCondor queue on
the workflow submit node.

The pegasus-plan command is used to plan a workflow. In the
mpi-example directory, run:

```shell
$ pegasus-plan --conf pegasus.conf \
  	       --sites sdsc-gordon \
	       --output-site local \
	       --dir work \
	       --dax mpi-hw.dax \
	        --nocleanup \
		--submit 
```

This takes the mpi-hw.dax and plans it against the sdsc-gordon site.
 --output-site means that we want the final outputs
of the workflow to come back to the submit host and the work directory
for the workflow should be work. The output of the command should
similar to: 

```
2015.07.14 18:52:59.607 EDT:    
2015.07.14 18:52:59.613 EDT:   ----------------------------------------------------------------------- 
2015.07.14 18:52:59.618 EDT:   File for submitting this DAG to Condor           : mpi-example-0.dag.condor.sub 
2015.07.14 18:52:59.623 EDT:   Log of DAGMan debugging messages                 : mpi-example-0.dag.dagman.out 
2015.07.14 18:52:59.629 EDT:   Log of Condor library output                     : mpi-example-0.dag.lib.out 
2015.07.14 18:52:59.634 EDT:   Log of Condor library error messages             : mpi-example-0.dag.lib.err 
2015.07.14 18:52:59.640 EDT:   Log of the life of condor_dagman itself          : mpi-example-0.dag.dagman.log 
2015.07.14 18:52:59.645 EDT:    
2015.07.14 18:52:59.661 EDT:   ----------------------------------------------------------------------- 
2015.07.14 18:53:00.645 EDT:   Your database is compatible with Pegasus version: 4.5.0 
2015.07.14 18:53:00.787 EDT:   Submitting to condor mpi-example-0.dag.condor.sub 
2015.07.14 18:53:01.315 EDT:   Submitting job(s). 
2015.07.14 18:53:01.321 EDT:   1 job(s) submitted to cluster 362. 
2015.07.14 18:53:01.327 EDT:    
2015.07.14 18:53:01.332 EDT:   Your workflow has been started and is running in the base directory: 
2015.07.14 18:53:01.338 EDT:    
2015.07.14 18:53:01.343 EDT:     /home/rynge/git/tutorials/XSEDE/mpi-example/work/rynge/pegasus/mpi-example/20150714T185255-0400 
2015.07.14 18:53:01.348 EDT:    
2015.07.14 18:53:01.354 EDT:   *** To monitor the workflow you can run *** 
2015.07.14 18:53:01.359 EDT:    
2015.07.14 18:53:01.365 EDT:     pegasus-status -l /home/rynge/git/tutorials/XSEDE/mpi-example/work/rynge/pegasus/mpi-example/20150714T185255-0400 
2015.07.14 18:53:01.370 EDT:    
2015.07.14 18:53:01.376 EDT:   *** To remove your workflow run *** 
2015.07.14 18:53:01.381 EDT:    
2015.07.14 18:53:01.387 EDT:     pegasus-remove /home/rynge/git/tutorials/XSEDE/mpi-example/work/rynge/pegasus/mpi-example/20150714T185255-0400 
2015.07.14 18:53:01.392 EDT:    
2015.07.14 18:53:01.717 EDT:   Time taken to execute is 5.629 seconds 
```

You can use the pegasus-status command to monitor the workflow. Once
the workflow finishes you can use the other pegasus commands and pass
them the workflow submit directory.  For your recap they are listed
below, and explained in the first example of this tutorial.

pegasus-analyzer     - to debug a failed workflow
pegasus-statistics   - to generate runtime statistics of your workflow.

If the workflow finishes successfully then in the outputs directory
you will see a file output.data. It will look something like this

```shell
$ cat outputs/output.data 
[Master] Total number of MPI processes 16
[Master] Hello world!  I am process number: 0 on host gcn-14-76.sdsc.edu
[Worker] Hello world!  I am process number: 3 on host gcn-14-76.sdsc.edu
[Worker] Hello world!  I am process number: 4 on host gcn-14-76.sdsc.edu
[Worker] Hello world!  I am process number: 5 on host gcn-14-76.sdsc.edu
[Worker] Hello world!  I am process number: 6 on host gcn-14-76.sdsc.edu
[Worker] Hello world!  I am process number: 2 on host gcn-14-76.sdsc.edu
[Worker] Hello world!  I am process number: 7 on host gcn-14-76.sdsc.edu
[Worker] Hello world!  I am process number: 1 on host gcn-14-76.sdsc.edu
[Worker] Hello world!  I am process number: 8 on host gcn-14-76.sdsc.edu
[Worker] Hello world!  I am process number: 9 on host gcn-14-76.sdsc.edu
[Worker] Hello world!  I am process number: 10 on host gcn-14-76.sdsc.edu
[Worker] Hello world!  I am process number: 11 on host gcn-14-76.sdsc.edu
[Worker] Hello world!  I am process number: 12 on host gcn-14-76.sdsc.edu
[Worker] Hello world!  I am process number: 13 on host gcn-14-76.sdsc.edu
[Worker] Hello world!  I am process number: 14 on host gcn-14-76.sdsc.edu
[Worker] Hello world!  I am process number: 15 on host gcn-14-76.sdsc.edu
```

## <a name="TOC-Example-3:-Running-small-tasks-as-with-pegasus-mpi-cluster"></a>Example 3: Running small tasks as with pegasus-mpi-cluster

<div>

Often, users have lots of short running single processor jobs in their workflow, that if submitted individually to the underlying PBS cluster take a long time to execute, as each job sits in the PBS queue. For example in our previous example, each job in the blackdiamond workflow actually runs for a minute each. However, since each job is submitted as a separate job to PBS, each job sits in the cluster PBS queue before it is executed. In order to alleviate this, it makes sense to cluster the short running jobs together. Pegasus allows users to cluster tasks in their workflow into larger chunks, and then execute them using a MPI based master worker tool called <span>_<span>**pegasus-mpi-cluster**</span>_</span> .

In this example, we take the same blackdiamond workflow that we ran previously and now run it using PMC where the whole workflow is clustered into a single MPI job. In order to tell Pegasus to cluster the jobs we have to do the following

<div style="color:rgb(51,51,51);font-family:Trebuchet MS,Arial,Helvetica,sans-serif;font-size:12px;line-height:18px">

1.  Tell Pegasus what jobs are clustered. In this example, we do it by annotating the DAX with a special pegasus profile called label. In the DAX generator BlackDiamondDAX.java you will see the following

    <pre style="font-family:Courier New,DejaVu Sans Mono,monospace;margin-top:1.364em;margin-bottom:1.364em;font-size:9pt;overflow:auto;border:1px solid gray;padding:10px;background-color:rgb(238,238,238)">        // Add a preprocess job
            System.out.println( "Adding preprocess job..." );
            Job j1 = new Job("j1", "pegasus", "preprocess", "4.0");
            j1.addArgument("-a preprocess -T 60 -i ").addArgument(fa);
            ...
            <span>**//associate the label with the job. all jobs with same label
            //are run with PMC when doing job clustering
            j1.addProfile( "pegasus", "label", "p1");**</span>

            dax.addJob(j1);</pre>

2.  Tell pegasus that it has to do job clustering and what executable to use for job clustering.

    To do this, you do the following

    <div>

    *   In pegasus.conf file specify the property <span>**pegasus.job.aggregator mpiexec**</span>

    *   In the transformation catalog, specify the path to the clustering executable. In this case, it is a wrapper around PMC that does mpiexec on pegasus-mpi-cluster. In conf/tc.dat you can see the last entry as

        <pre style="font-family:Courier New,DejaVu Sans Mono,monospace;margin-top:1.364em;margin-bottom:1.364em;font-size:9pt;overflow:auto;border:1px solid gray;padding:10px;background-color:rgb(238,238,238)">[userXX@hpc-pegasus mpi-hello-world]$ <span>**tail conf/tc.dat**</span>

        # pegasus mpi clustering executable
        tr pegasus::mpiexec{
            site hpcc {
                pfn "/home/rcf-proj/gmj/pegasus/SOFTWARE/pegasus/pegasus-mpi-cluster-wrapper"
                arch "x86_64"
                os "linux"
                type "INSTALLED"
                profile pegasus "clusters.size" "2"

                #the various parameters to specify the size of the MPI job
                #in which the workflow runs
                profile globus "jobtype" "mpi"
                profile globus "maxwalltime" "2880"
                # specfiy the ppn parameter.
                profile globus "xcount" "4:IB"
                # specify the nodes parameter
                profile globus "hostcount" "1"
                #specify the pmem parameter
                profile globus "maxmemory" "1gb"
                #specify the mem parameter
                profile globus "totalmemory" "16gb"

            }
        }
        </pre>

        The profiles tell Pegasus that the PMC executable needs to be run on 4 processors on a single node, process per process as 1GB and total memory on the node as 16GB.

    </div>

3.  Lastly, while planning the workflow we add <span>**--cluster** </span>option to pegasus-plan. That is what we have in plan_cluster_dax.sh file.

    <pre style="font-family:Courier New,DejaVu Sans Mono,monospace;margin-top:1.364em;margin-bottom:1.364em;font-size:9pt;overflow:auto;border:1px solid gray;padding:10px;background-color:rgb(238,238,238)">$ <span>**cat plan_cluster_dax.sh**</span>

    #!/bin/sh

    set -e

    # plan and submit the  workflow
    pegasus-plan \
        --conf pegasus.conf \
        --sites hpcc \
        --output-site local \
        --dir work/dags \
        --dax diamond.dax \
        -v \
        --force \
        --nocleanup \
        --cluster label
    </pre>

</div>

<span>**Let us now plan and run the workflow.**</span>

<pre style="font-family:Courier New,DejaVu Sans Mono,monospace;margin-top:1.364em;margin-bottom:1.364em;font-size:12px;overflow:auto;border:1px solid gray;padding:10px;color:rgb(51,51,51);line-height:18px;background-color:rgb(238,238,238)">[userXX@hpc-pegasus mpi-hello-world]$ <span>**./<span>**plan_cluster_dax.sh**</span>**</span>

[vahi@hpc-pegasus blackdiamond-hpcc-sharedfs-example]$ ./plan_cluster_dax.sh
2014.12.18 15:29:25.877 PST: [WARNING]  --nocleanup option is deprecated. Use --cleanup none
2014.12.18 15:29:25.885 PST: [INFO]  Planner invoked with following arguments --conf pegasus.conf --sites hpcc --output-site local --dir work/dags --dax diamond.dax -v --force --nocleanup --cluster label --submit
2014.12.18 15:29:26.304 PST: [INFO] event.pegasus.parse.dax dax.id /auto/rcf-40/vahi/tutorial/blackdiamond-hpcc-sharedfs-example/diamond.dax  - STARTED
2014.12.18 15:29:26.306 PST: [INFO] event.pegasus.parse.dax dax.id /auto/rcf-40/vahi/tutorial/blackdiamond-hpcc-sharedfs-example/diamond.dax  (0.002 seconds) - FINISHED
2014.12.18 15:29:26.309 PST: [INFO] event.pegasus.parse.dax dax.id /auto/rcf-40/vahi/tutorial/blackdiamond-hpcc-sharedfs-example/diamond.dax  - STARTED
2014.12.18 15:29:26.349 PST: [INFO] event.pegasus.add.data-dependencies dax.id blackdiamond_0  - STARTED
2014.12.18 15:29:26.350 PST: [INFO] event.pegasus.add.data-dependencies dax.id blackdiamond_0  (0.001 seconds) - FINISHED
2014.12.18 15:29:26.350 PST: [INFO] event.pegasus.parse.dax dax.id /auto/rcf-40/vahi/tutorial/blackdiamond-hpcc-sharedfs-example/diamond.dax  (0.041 seconds) - FINISHED
2014.12.18 15:29:26.386 PST: [INFO] event.pegasus.stampede.events dax.id blackdiamond_0  - STARTED
2014.12.18 15:29:26.440 PST: [INFO] event.pegasus.stampede.events dax.id blackdiamond_0  (0.054 seconds) - FINISHED
2014.12.18 15:29:26.442 PST: [INFO] event.pegasus.refinement dax.id blackdiamond_0  - STARTED
2014.12.18 15:29:26.479 PST: [INFO] event.pegasus.siteselection dax.id blackdiamond_0  - STARTED
2014.12.18 15:29:26.492 PST: [INFO] event.pegasus.siteselection dax.id blackdiamond_0  (0.013 seconds) - FINISHED
2014.12.18 15:29:26.509 PST: [INFO] event.pegasus.cluster dax.id blackdiamond_0  - STARTED
2014.12.18 15:29:26.542 PST: [INFO]  Starting Graph Traversal
2014.12.18 15:29:26.544 PST: [INFO]  Starting Graph Traversal - DONE
2014.12.18 15:29:26.549 PST: [INFO]  Determining relations between partitions
2014.12.18 15:29:26.549 PST: [INFO]  Determining relations between partitions - DONE
2014.12.18 15:29:26.549 PST: [INFO] event.pegasus.cluster dax.id blackdiamond_0  (0.04 seconds) - FINISHED
2014.12.18 15:29:26.554 PST: [INFO]  Grafting transfer nodes in the workflow
2014.12.18 15:29:26.554 PST: [INFO] event.pegasus.generate.transfer-nodes dax.id blackdiamond_0  - STARTED
2014.12.18 15:29:26.651 PST: [INFO] event.pegasus.generate.transfer-nodes dax.id blackdiamond_0  (0.097 seconds) - FINISHED
2014.12.18 15:29:26.651 PST: [INFO] event.pegasus.generate.workdir-nodes dax.id blackdiamond_0  - STARTED
2014.12.18 15:29:26.660 PST: [INFO] event.pegasus.generate.workdir-nodes dax.id blackdiamond_0  (0.009 seconds) - FINISHED
2014.12.18 15:29:26.660 PST: [INFO] event.pegasus.refinement dax.id blackdiamond_0  (0.218 seconds) - FINISHED
2014.12.18 15:29:26.706 PST: [INFO]  Generating codes for the executable workflow
2014.12.18 15:29:26.707 PST: [INFO] event.pegasus.code.generation dax.id blackdiamond_0  - STARTED
2014.12.18 15:29:26.920 PST: [INFO] event.pegasus.code.generation dax.id blackdiamond_0  (0.213 seconds) - FINISHED
2014.12.18 15:29:27.403 PST:   Submitting job(s).
2014.12.18 15:29:27.409 PST:   1 job(s) submitted to cluster 2290\.
2014.12.18 15:29:27.414 PST:
2014.12.18 15:29:27.420 PST:   -----------------------------------------------------------------------
2014.12.18 15:29:27.425 PST:   File for submitting this DAG to Condor           : blackdiamond-0.dag.condor.sub
2014.12.18 15:29:27.430 PST:   Log of DAGMan debugging messages                 : blackdiamond-0.dag.dagman.out
2014.12.18 15:29:27.436 PST:   Log of Condor library output                     : blackdiamond-0.dag.lib.out
2014.12.18 15:29:27.441 PST:   Log of Condor library error messages             : blackdiamond-0.dag.lib.err
2014.12.18 15:29:27.446 PST:   Log of the life of condor_dagman itself          : blackdiamond-0.dag.dagman.log
2014.12.18 15:29:27.452 PST:
2014.12.18 15:29:27.457 PST:   -----------------------------------------------------------------------
2014.12.18 15:29:27.462 PST:
2014.12.18 15:29:27.468 PST:   Your workflow has been started and is running in the base directory:
2014.12.18 15:29:27.473 PST:
2014.12.18 15:29:27.478 PST:     /auto/rcf-40/vahi/tutorial/blackdiamond-hpcc-sharedfs-example/work/dags/vahi/pegasus/blackdiamond/run0002
2014.12.18 15:29:27.483 PST:
2014.12.18 15:29:27.489 PST:   *** To monitor the workflow you can run ***
2014.12.18 15:29:27.494 PST:
2014.12.18 15:29:27.499 PST:     pegasus-status -l /auto/rcf-40/vahi/tutorial/blackdiamond-hpcc-sharedfs-example/work/dags/vahi/pegasus/blackdiamond/run0002
2014.12.18 15:29:27.505 PST:
2014.12.18 15:29:27.510 PST:   *** To remove your workflow run ***
2014.12.18 15:29:27.515 PST:
2014.12.18 15:29:27.521 PST:     pegasus-remove /auto/rcf-40/vahi/tutorial/blackdiamond-hpcc-sharedfs-example/work/dags/vahi/pegasus/blackdiamond/run0002
2014.12.18 15:29:27.526 PST:
2014.12.18 15:29:28.334 PST:   Time taken to execute is 1.708 seconds
2014.12.18 15:29:28.334 PST: [INFO] event.pegasus.planner planner.version 4.4.1cvs  (2.463 seconds) - FINISHED </pre>

This is what the diamond workflow looks like after Pegasus has finished planning the DAX:

<div style="color:rgb(51,51,51);font-family:Trebuchet MS,Arial,Helvetica,sans-serif;font-size:12px;line-height:18px"><a name="idm268856400032" style="color:rgb(63,116,171)"></a>

**Figure 1.8\. Clustered Diamond DAG**

<div>

<div>![Clustered Diamond DAG](https://pegasus.isi.edu/tutorial/usc14/images/concepts-clustered-diamond-dag.jpg)</div>

</div>

</div>

You can see that instead of 4 jobs making up the diamond have been replaced by a single merge_p1 job, that is executed as a MPI job.

</div>

</div>

