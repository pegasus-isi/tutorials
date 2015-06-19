#!/usr/bin/env python

from Pegasus.DAX3 import *
import sys
import os

# Create a abstract dag
dax = ADAG("mpi-example")

# We have three files: one existing input, one intermediate
# generated at site 1, and one ouput generated at site 2
finput = File("input.data")
finput.addPFN(PFN("file://" + os.getcwd() + "/input.data", "local"))
dax.addFile(finput)

foutput = File("output.data")

# step 1 (to run on site 1)
job1 = Job(name="mpi-hello-world")
job1.addArguments("-o", foutput)
job1.uses(finput, link=Link.INPUT)
job1.uses(foutput, link=Link.OUTPUT, transfer=True)
dax.addJob(job1)

# Write the DAX to stdout
dax.writeXML(sys.stdout)


