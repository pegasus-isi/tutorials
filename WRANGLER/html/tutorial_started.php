<?php  
            require('/srv/new-pegasus.isi.edu/includes/common.php'); 
            pegasus_header("1.2. Getting Started");
        ?><div class="breadcrumbs">
<span class="breadcrumb-link"><a href="index.php">Scientific Workflows through Pegasus WMS at USC HPC Cluster</a></span> &gt; <span class="breadcrumb-link"><a href="tutorial.php">Tutorial</a></span> &gt; <span class="breadcrumb-node">Getting Started</span>
</div><hr><div class="section">
<div class="titlepage"><div><div><h2 class="title" style="clear: both">
<a name="tutorial_started"></a>1.2. Getting Started</h2></div></div></div>
<p>All of the steps in this tutorial are performed on the command-line.
    The convention we will use for command-line input and output is to put
    things that you should type in bold, monospace font, and to put the output
    you should get in a normal weight, monospace font, like this:</p>
<pre class="programlisting">[user@hpc-pegasus]$ <span class="bold"><strong>you type this</strong></span>
you get this</pre>
<p>Where <code class="literal">[user@host dir]$</code> is the terminal prompt,
    the text you should type is “<code class="literal">you type this</code>”, and the
    output you should get is "<code class="literal">you get this</code>". The terminal
    prompt will be abbreviated as <code class="literal">$</code>. Because some of the
    outputs are long, we don’t always include everything. Where the output is
    truncated we will add an ellipsis '...' to indicate the omitted
    output.</p>
<p>Login to the hpc-pegasus.usc.edu submit node</p>
<pre class="programlisting">$ <span class="bold"><strong>ssh hpc-pegasus.usc.edu</strong></span>
*************************************************************************

19940429  All users of this computer system acknowledge that activities on it
          may be subject to monitoring;  the privacy of activities on this
          computer cannot be ensured.  All computer account users are required
          to read and abide by the ITS Computing and Usage Policies.  Please
          refer to the web page at:
		http://www.usc.edu/its/policies/computing/

*************************************************************************
[userXX@hpc-pegasus ~]$ 

</pre>
<div class="note" style="margin-left: 0.5in; margin-right: 0.5in;">
<h3 class="title">Note</h3>
<p>For the purpose of this tutorial replace any instance of trainXX
      with your hpc-pegasus.usc.edu username.</p>
</div>
<p><span class="bold"><strong>If you are having trouble with this tutorial,
    or anything else related to Pegasus, you can contact the Pegasus Users
    mailing list at <code class="email">&lt;<a class="email" href="mailto:pegasus-users@isi.edu">pegasus-users@isi.edu</a>&gt;</code> to get
    help.</strong></span></p>
<p>The tutorial should be done in the bash shell. Lets make sure that
    you are in the right shell.</p>
<pre class="programlisting">[userXX@hpc-pegasus]$  bash
[userXX@hpc-pegasus ~]$ echo $SHELL
/bin/bash
</pre>
<div class="note" style="margin-left: 0.5in; margin-right: 0.5in;">
<h3 class="title">Note</h3>
<p>In order to make it easier for USC users to start with Pegasus on
      USC HPCC, we have a dedicated submit node <span class="bold"><strong>hpc-pegasus.usc.edu</strong></span> that users can use to do the
      tutorial and submit their workflows to the HPC cluster using Pegasus. To
      request access to the submit node, please send me to the HPCC staff
      <span class="bold"><strong><code class="email">&lt;<a class="email" href="mailto:hpcc@usc.edu">hpcc@usc.edu</a>&gt;</code> </strong></span>. Usually,
      getting an account on the submit machines take one business day or
      less.</p>
<p></p>
</div>
</div><div class="navfooter">
<hr>
<table width="100%" summary="Navigation footer">
<tr>
<td width="40%" align="left">
<a accesskey="p" href="tutorial.php">Prev</a> </td>
<td width="20%" align="center"><a accesskey="u" href="tutorial.php">Up</a></td>
<td width="40%" align="right"> <a accesskey="n" href="tutorial_scientific_workflows.php">Next</a>
</td>
</tr>
<tr>
<td width="40%" align="left" valign="top">Chapter 1. Tutorial </td>
<td width="20%" align="center"><a accesskey="h" href="index.php">Table of Contents</a></td>
<td width="40%" align="right" valign="top"> 1.3. What are Scientific Workflows</td>
</tr>
</table>
</div><?php  
            pegasus_footer();
        ?>
