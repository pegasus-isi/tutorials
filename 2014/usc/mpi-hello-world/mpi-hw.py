#!/usr/bin/env python

from Pegasus.DAX3 import *
import sys
import os


# Create a abstract dag
mpi_hw_wf = ADAG("mpi-hello-world")

# Add input file to the DAX-level replica catalog
fin = File("fin")
fin.addPFN(PFN("file://" + os.getcwd() + "/f.in", "hpcc"))
mpi_hw_wf.addFile(fin)
        
# Add executables to the DAX-level transformation catalog
# For submitting MPI jobs directly through condor without GRAM
# we need to refer to wrapper that calls mpiexec with 
# the mpi executable
e_mpi_hw = Executable(namespace="pegasus", name="mpihw", os="linux", arch="x86_64", installed=True)
e_mpi_hw.addPFN(PFN("file://" + os.getcwd() + "/mpi-hello-world-wrapper", "hpcc"))
mpi_hw_wf.addExecutable(e_mpi_hw)


# Add the mpi hello world job
mpi_hw_job = Job(namespace="pegasus", name="mpihw" )
fout = File("f.out")
mpi_hw_job.addArguments("-o ", fout )
mpi_hw_job.uses(fin, link=Link.INPUT)
mpi_hw_job.uses(fout, link=Link.OUTPUT)

# tell pegasus it is an MPI job
mpi_hw_job.addProfile( Profile( "globus", "jobtype", "mpi"))

# add profiles indicating PBS specific parameters for HPCC
# the globus key hostCount is NODES
mpi_hw_job.addProfile( Profile("globus", "hostcount", "1" ))
# the globus key xcount is PROCS or PPN
mpi_hw_job.addProfile( Profile("globus", "xcount", "8" ))    
#  the globus key maxwalltime is WALLTIME in minutes
mpi_hw_job.addProfile( Profile("globus", "maxwalltime", "60"))
mpi_hw_wf.addJob(mpi_hw_job)

# Write the DAX to stdout
mpi_hw_wf.writeXML(sys.stdout)
