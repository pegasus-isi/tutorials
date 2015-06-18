
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

![DAX](/figures/dax.dot.png)


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

Test

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



