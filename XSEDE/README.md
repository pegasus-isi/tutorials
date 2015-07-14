
This tutorial will take you through the steps of creating and
running a simple scientific workflow using Pegasus Workflow
Management System (WMS) on XSEDE. This tutorial is intended
for new users who want to get a quick overview of Pegasus
concepts and usage. More information about the topics covered
in this tutorial can be found in the Pegasus user guide at
 [http://pegasus.isi.edu/wms/docs/latest/](http://pegasus.isi.edu/wms/docs/latest/)

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

## Example 1: Two sites

As a first example we will run a very simple workflow: two tasks, named
*step1* and *step2*, and *step2* having a dependency on *step1*:

![DAX](https://raw.githubusercontent.com/pegasus-isi/tutorials/master/XSEDE/figures/dax.dot.png)

Dependencies generally represent data file dependencies. In this
workflow, *step1* takes an input file named *input.data*, and generates
a file named *intermediate.data*. *Step2* takes the *intermediate.data* as
input, and produces a file named *output.data*. With the data pieces
included, the workflow figure now looks like:

![DAX with files](https://raw.githubusercontent.com/pegasus-isi/tutorials/master/XSEDE/figures/dax-with-files.dot.png)

It is the workflow system's job to keep track of the data, and execute
the tasks in the correct order. Later in the tutorial, we will see how
Pegasus executes a workflow like this.

To make this example a little bit more interesting for XSEDE, we will
tell Pegasus that *step1* will execute on one XSEDE site, while *step2* will
execute on another, and let Pegasus handle the required data movements
to make such a scenario possible.

To get started, make a copy of the tutorial files to your home directory on workflow.iu.xsede.org, and then move into the two-xsede-sites example. Run:

```shell
$ cp -r /opt/pegasus/tutorial ~/pegasus-tutorial
$ cd ~/pegasus-tutorial/two-xsede-sites
```

## Defining a workflow

Pegasus reads workflow descriptions from DAX files. The term “DAX”
is short for “Directed Acyclic Graph in XML”. DAX is an XML file
format that has syntax for expressing jobs, arguments, files, and
dependencies. In order to create a DAX it is necessary to write code
for a DAX generator. Pegasus comes with Perl, Java, and Python APIs for
writing DAX generators. In this tutorial we will show how to use the
Python API.
 [http://pegasus.isi.edu/wms/docs/latest/python/](http://pegasus.isi.edu/wms/docs/latest/python/)

A DAX generator for the workflow described above is included in the file
named dax-generator.py, which has the content:

```python
#!/usr/bin/env python

from Pegasus.DAX3 import *
import sys
import os

# Create a abstract dag
dax = ADAG("two-xsede-sites")

# We have three files: one existing input, one intermediate
# generated at site 1, and one ouput generated at site 2
finput = File("input.data")
finput.addPFN(PFN("file://" + os.getcwd() + "/input.data", "local"))
dax.addFile(finput)

fintermediate = File("intermediate.data")
foutput = File("output.data")

# step 1 (to run on site 1)
job1 = Job(name="step1")
job1.addArguments("-T", "60", "-i", finput, "-o", fintermediate)
job1.uses(finput, link=Link.INPUT)
job1.uses(fintermediate, link=Link.OUTPUT, transfer=False)
dax.addJob(job1)

# step 2 (to run on site 2)
job2 = Job(name="step2")
job2.addArguments("-T", "60", "-i", fintermediate, "-o", foutput)
job2.uses(fintermediate, link=Link.INPUT)
job2.uses(foutput, link=Link.OUTPUT, transfer=True)
dax.addJob(job2)

# job dependencies
dax.depends(parent=job1, child=job2)

# Write the DAX to stdout
dax.writeXML(sys.stdout)
```

The code has 3 sections:

1. A new ADAG object is created. This is the main object to which tasks and dependencies are added.
2. Tasks and files are added. The 2 tasks in the diagram above are added and the 3 files are referenced. Arguments are defined using strings and File objects. The input and output files are defined for each job. This is an important step, as it allows Pegasus to track the files, and stage the data if necessary. Final workflow outputs are tagged with “transfer=true”.
3. Dependencies are added. These are shown as arrows in the diagram above. They define the parent/child relationships between the jobs. When the workflow is executing, the order in which the jobs will be run is determined by the dependencies between them.

To generate the DAX and see what it looks like, run:

```shell
$ ./dax-generator.py >workflow.dax
$ cat workflow.dax
```

Note that neither the Python script, nor the DAX contains any execution
environment information. This is why the DAX is an abstract workflow,
and it is what provides portability of the workflow. We can now map this
abstract workflow for execution in different execution environments. To
fill in the missing pieces, Pegasus uses catalogs.

## Catalogs

There are three information catalogs that Pegasus uses when planning
the workflow. These are the *Site* catalog, *Transformation* catalog, and
*Replica* catalog.

### Site Catalog

The site catalog describes the sites where the workflow jobs are to
be executed. Typically the sites in the site catalog describe remote
clusters, such as Slurm clusters or HTCondor pools. In this tutorial we
have to create a catalog with specific values for your XSEDE user. This
is mostly automated, but minor edits to the *generate-site-catalog.sh* are
required. At the minimum, you will have to specify an allocation. Open
up the *generate-site-catalog.sh* script in your favorite text editor. For
example:

```shell
$ nano generate-site-catalog.sh
```

In the first section, replace the value for the *ALLOCATION* variable.
Then generate a site catalog:

```shell
$ ./generate-site-catalog.sh
```

The resulting file, sites.xml, should look something like:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<sitecatalog xmlns="http://pegasus.isi.edu/schema/sitecatalog" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://pegasus.isi.edu/schema/sitecatalog http://pegasus.isi.edu/schema/sc-4.0.xsd" version="4.0">

    <site  handle="local" arch="x86" os="LINUX">
        <!-- the base directory where workflow jobs will execute for local site -->
        <directory type="shared-scratch" path="/home/rynge/pegasus/two-xsede-sites/work/scratch">
            <file-server operation="all" url="file:///home/rynge/pegasus/two-xsede-sites/work/scratch"/>
        </directory>

        <!-- the directory where outputs will be placed  -->
        <directory type="local-storage" path="/home/rynge/pegasus/two-xsede-sites/outputs">
            <file-server operation="all" url="file:///home/rynge/pegasus/two-xsede-sites/outputs"/>
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

    <site  handle="tacc-stampede" arch="x86_64" os="LINUX">
        <grid  type="gt5" contact="login5.stampede.tacc.utexas.edu:2119/jobmanager-fork" scheduler="Fork" jobtype="auxillary"/>
        <grid  type="gt5" contact="login5.stampede.tacc.utexas.edu:2119/jobmanager-slurm" scheduler="unknown" jobtype="compute"/>

        <directory type="shared-scratch" path="/scratch/00384/rynge/workflow-runs">
            <file-server operation="all" url="gsiftp://gridftp.stampede.tacc.xsede.org:2811/scratch/00384/rynge/workflow-runs"/>
        </directory>

        <profile namespace="globus" key="project">TG-STA110014S</profile>
        <profile namespace="env" key="PEGASUS_HOME">/home1/00384/rynge/software/pegasus/4.4.1</profile>
    </site>

</sitecatalog>
```
There are three sites defined in the site catalog: *local*,
*sdsc-gordon* and *tacc-stampede*. The *local* site is used by Pegasus
to learn about the submit host where the workflow management system
runs. The two other sites describe XSEDE resources.

The local site is configured with a *local-storage* file system that is
mounted on the submit host (indicated by the file:// URL). This file
system is where the output data from the workflow will be stored. When
the workflow is planned we will tell Pegasus that the output site is
*local*.

The XSEDE sites are configured with *shared-scratch* file systems accessible
via GridFTP (indicated by the *gsiftp://* URL). This file system is where
the working directory will be created. When we plan the workflow we will
tell Pegasus that the available execution sites are *sdsc-gordon* and
*tacc-stampede*.

Pegasus supports many different file transfer protocols. For example,
you can set up transfers from your submit host to the cluster using SCP.
In that case, the scratch file system with have a *scp://* URL. To specify
the passwordless ssh key to use, you will need to add a pegasus profile
key named SSH_PRIVATE_KEY that tells Pegasus where to find the private
key to use for SCP transfers. Remember to add the passwordless key to
your ssh authorized keys.
 [http://pegasus.isi.edu/wms/docs/latest/transfer.php](http://pegasus.isi.edu/wms/docs/latest/transfer.php)

Finally, the XSEDE sites are configured with profiles which in this
case defines where Pegasus is installed on the remote site, and what
alloaction (project) to charge the jobs to.


### Transformation Catalog

The transformation catalog describes all of the executables (called
*transformations*) used by the workflow. This description includes
the site(s) where they are located, the architecture and operating
system they are compiled for, and any other information required to
properly transfer them to the execution site and run them.

For this tutorial, the transformation catalog is in the file
*transformation.catalog*:

```
tr step1 {
    site sdsc-gordon {
        pfn "/home/ux454281/software/pegasus/pegasus-4.5.0/bin/pegasus-keg"
        arch "x86_64"
        os "linux"
        type "INSTALLED"
        profile globus "maxwalltime" "10"
    }
}

tr step2 {
    site tacc-stampede {
        pfn "/home1/00384/rynge/software/pegasus/4.4.1/bin/pegasus-keg"
        arch "x86_64"
        os "linux"
        type "INSTALLED"
        profile globus "maxwalltime" "10"
        profile globus "queue" "development"
    }
}
```

Note that the two executables, *step1* and *step2*, are installed on
different resources. When Pegasus plans a workflow, it will look up
where an executable exists, and map the jobs accordingly. You can have
same exectuable installed at multiple resources, and in that case,
Pegasus will pick where the job will go for you.

The binary in this example, *pegasus-keg*, is just small tool used in
testing. To simulate a real code, *pegasus-keg* takes some inputs,
outputs, and does a little bit of processing.


### Replica Catalog

The final catalog is the *Replica* catalog. This catalog tells Pegasus
where to find each of the input files for the workflow.

All files in a Pegasus workflow are referred to in the DAX using their
Logical File Name (LFN). These LFNs are mapped to Physical File Names
(PFNs) when Pegasus plans the workflow. This level of indirection
enables Pegasus to map abstract DAXes to different execution sites and
plan out the required file transfers automatically.

It is possible to use a file based replica catalog, but in this case
we used a shortcut. Scroll back up to the *dax-generator.py* code, and
take a look at the following lines:

```python
finput = File("input.data")
finput.addPFN(PFN("file://" + os.getcwd() + "/input.data", "local"))
dax.addFile(finput)
```

This is a convenience method for including the replica catalog directly
inside the DAX. It does make the DAX a little bit less portable, but
many users generate DAXes on the fly, and it is easier to keep the
replica catalog close to where the files are used in the DAX.

## Planning a workflow

The planning stage is where Pegasus maps the abstract DAX to one or more execution sites. The planning step includes:

1. Adding a job to create the remote working directory
2. Adding stage-in jobs to transfer input data to the remote working directory
3. Adding cleanup jobs to remove data from the remote working directory when it is no longer needed
4. Adding stage-out jobs to transfer data to the final output location as it is generated
5. Adding registration jobs to register the data in a replica catalog
6. Task clustering to combine several short-running jobs into a single, longer-running job. This is done to make short-running jobs more efficient.
7. Adding wrappers to the jobs to collect provenance information so that statistics and plots can be created when the workflow is finished

The *pegasus-plan* command is used to plan a workflow. In the *two-xsede-sites* directory, run:

```shell
pegasus-plan \
    --conf pegasus.conf \
    --sites sdsc-gordon,tacc-stampede \
    --output-site local \
    --dir work \
    --dax workflow.dax
```

This takes the *workflow.dax* and plans it against the *sdsc-gordon*
and *sdsc-gordon* sites. *--output-site* means that we want the final
outputs of the workflow to come back to the submit host and the work
directory for the workflow should be *work*. The output of the command
should similar to:

```
I have concretized your abstract workflow. The workflow has been entered
into the workflow database with a state of "planned". The next step is
to start or execute your workflow. The invocation required is


pegasus-run  /home/rynge/pegasus/two-xsede-sites/work/rynge/pegasus/two-xsede-sites/20150618T164355-0400
```

Note the line in the output that starts with *pegasus-run*. That is the
command that we will use to submit the workflow. The path it contains
is the path to the submit directory where all of the files required to
submit and monitor the workflow are stored. This directory path is the
handle to the workflow, and is used with more Pegasus commands later
on.

If you are curious, you can look inside the directory. You will see a
bunch of HTCondor submit files and other management data and logs.

This is what the workflow looks like after Pegasus has finished planning the DAX:

![DAG](https://raw.githubusercontent.com/pegasus-isi/tutorials/master/XSEDE/figures/dag.dot.png)

Note all the extra data staging and cleanup jobs Pegasus has added.


## Submitting a workflow for execution

Once the workflow has been planned, the next step is to submit it to
DAGMan/HTCondor for execution. This is done using the *pegasus-run*
command. This command takes the path to the submit directory as an
argument. Run the command that was printed by the *plan_dax.sh* script:

```shell
$ pegasus-run  /YOUR/WF/PATH
Submitting to condor two-xsede-sites-0.dag.condor.sub
Submitting job(s).
1 job(s) submitted to cluster 271.

Your workflow has been started and is running in the base directory:

  /YOUR/WF/PATH

*** To monitor the workflow you can run ***

  pegasus-status -l /YOUR/WF/PATH

*** To remove your workflow run ***

  pegasus-remove /YOUR/WF/PATH
```

## Monitoring, statistics and debugging

All the command line interfaces described so far, and the ones
following, all have man pages which describe the command and arguments.
If you prefer a web version, they can be found at
 [http://pegasus.isi.edu/wms/docs/latest/cli.php](http://pegasus.isi.edu/wms/docs/latest/cli.php)

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

This takes the mpi-hw.dax and plans it against the sdsc-gordon and
sdsc-gordon sites. --output-site means that we want the final outputs
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

