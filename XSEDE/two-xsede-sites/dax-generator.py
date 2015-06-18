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


