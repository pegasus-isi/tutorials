#!/usr/bin/env python

import os
import sys
from Pegasus.DAX3 import *

# The name of the DAX file is the first argument                                                                                                                                                                                                                                                                                                 
if len(sys.argv) != 2:
        sys.stderr.write("Usage: %s DAXFILE\n" % (sys.argv[0]))
        sys.exit(1)
daxfile = sys.argv[1]

dax = ADAG("split")

webpage = File("pegasus.html")

curl = Job("curl")
curl.addArguments("-o",webpage,"http://pegasus.isi.edu")
curl.uses(webpage, link=Link.OUTPUT)
dax.addJob(curl)

split = Job("split")
split.addArguments("-l","100","-a","1",webpage,"part.")
split.uses(webpage, link=Link.INPUT)
dax.addJob(split)

dax.depends(split, curl)

for c in "abcd":
    part = File("part.%s" % c)
    split.uses(part, link=Link.OUTPUT)

    count = File("count.txt.%s" % c)

    wc = Job("wc")
    wc.addArguments("-l",part)
    wc.setStdout(count)
    wc.uses(part, link=Link.INPUT)
    wc.uses(count, link=Link.OUTPUT, transfer=True, register=False)
    dax.addJob(wc)

    dax.depends(wc, split)


f = open(daxfile, "w")
dax.writeXML(f)
f.close()

