<?php  
            require('/srv/new-pegasus.isi.edu/includes/common.php'); 
            pegasus_header("Chapter 1. Tutorial");
        ?><div class="breadcrumbs">
<span class="breadcrumb-link"><a href="index.php">Scientific Workflows through Pegasus WMS at USC HPC Cluster</a></span> &gt; <span class="breadcrumb-node">Tutorial</span>
</div><hr><div class="chapter">
<div class="titlepage"><div><div><h1 class="title">
<a name="tutorial"></a>Chapter 1. Tutorial</h1></div></div></div>
<div class="toc"><dl class="toc">
<dt><span class="section"><a href="tutorial.php#tutorial_introduction">1.1. Introduction</a></span></dt>
<dt><span class="section"><a href="tutorial_started.php">1.2. Getting Started</a></span></dt>
<dt><span class="section"><a href="tutorial_scientific_workflows.php">1.3. What are Scientific Workflows</a></span></dt>
<dt><span class="section"><a href="tutorial_submitting_wf.php">1.4. Submitting an Example Workflow</a></span></dt>
<dt><span class="section"><a href="tutorial_wf_generation.php">1.5. Generating the Workflow</a></span></dt>
<dt><span class="section"><a href="tutorial_catalogs.php">1.6. Information Catalogs</a></span></dt>
<dt><span class="section"><a href="tutorial_configuration.php">1.7. Configuring Pegasus</a></span></dt>
<dt><span class="section"><a href="tutorial_wf_dashboard.php">1.8. Workflow Dashboard for Monitoring and Debugging</a></span></dt>
<dt><span class="section"><a href="tutorial_monitoring_cmd_tools.php">1.9. Command line tools for Monitoring and Debugging</a></span></dt>
<dt><span class="section"><a href="tutorial_failure_recovery.php">1.10. Recovery from Failures</a></span></dt>
<dt><span class="section"><a href="tutorial_pmc.php">1.11. Running the whole workflow as an MPI job</a></span></dt>
<dt><span class="section"><a href="tutorial_conclusion.php">1.12. Conclusion</a></span></dt>
</dl></div>
<div class="section">
<div class="titlepage"><div><div><h2 class="title" style="clear: both">
<a name="tutorial_introduction"></a>1.1. Introduction</h2></div></div></div>
<p>This tutorial will take you through the steps of running simple
    workflows using Pegasus Workflow Management System. Pegasus allows
    scientists to</p>
<div class="orderedlist"><ol class="orderedlist" type="1">
<li class="listitem"><p><span class="bold"><strong>Automate</strong></span> their scientific
        computational work, as portable workflows. Pegasus enables scientists
        to construct workflows in abstract terms without worrying about the
        details of the underlying execution environment or the particulars of
        the low-level specifications required by the middleware (Condor,
        Globus, or Amazon EC2). It automatically locates the necessary input
        data and computational resources necessary for workflow execution. It
        cleans up storage as the workflow is executed so that data-intensive
        workflows have enough space to execute on storage-constrained
        resources.</p></li>
<li class="listitem"><p><span class="bold"><strong>Recover</strong></span> from failures at
        runtime. When errors occur, Pegasus tries to recover when possible by
        retrying tasks, and when all else fails, provides a rescue workflow
        containing a description of only the work that remains to be done. It
        also enables users to move computations from one resource to another.
        Pegasus keeps track of what has been done (provenance) including the
        locations of data used and produced, and which software was used with
        which parameters.</p></li>
<li class="listitem"><p><span class="bold"><strong>Debug</strong></span> failures in their
        computations using a set of system provided debugging tools and an
        online workflow monitoring dashboard.</p></li>
</ol></div>
<p>This tutorial is intended for new users who want to get a quick
    overview of Pegasus concepts and usage. The accompanying tutorial VM comes
    pre-configured to run the example workflows. The instructions listed here
    refer mainly to the simple split workflow example. The tutorial
    covers</p>
<div class="itemizedlist"><ul class="itemizedlist" style="list-style-type: disc; ">
<li class="listitem"><p>submission of an already generated example workflow with
        Pegasus.</p></li>
<li class="listitem"><p>information catalogs configuration.</p></li>
<li class="listitem"><p>creation of workflow using system provided API</p></li>
<li class="listitem"><p>how to use the Pegasus Workflow Dashboard for monitoring
        workflows.</p></li>
<li class="listitem"><p>the command line tools for monitoring, debugging and generating
        statistics.</p></li>
<li class="listitem"><p>recovery from failures</p></li>
<li class="listitem"><p>running the whole workflow as a single MPI job using
        pegasus-mpi-cluster .</p></li>
</ul></div>
<p>More information about the topics covered in this tutorial can be
    found in later chapters of this user's guide.</p>
<p>All of the steps in this tutorial are performed on the command-line.
    The convention we will use for command-line input and output is to put
    things that you should type in bold, monospace font, and to put the output
    you should get in a normal weight, monospace font, like this:</p>
<pre class="programlisting">[user@host dir]$ <span class="bold"><strong>you type this</strong></span>
you get this</pre>
<p>Where <code class="literal">[user@host dir]$</code> is the terminal prompt,
    the text you should type is “<code class="literal">you type this</code>”, and the
    output you should get is "<code class="literal">you get this</code>". The terminal
    prompt will be abbreviated as <code class="literal">$</code>. Because some of the
    outputs are long, we don’t always include everything. Where the output is
    truncated we will add an ellipsis '...' to indicate the omitted
    output.</p>
<p><span class="bold"><strong>If you are having trouble with this tutorial,
    or anything else related to Pegasus, you can contact the Pegasus Users
    mailing list at <code class="email">&lt;<a class="email" href="mailto:pegasus-users@isi.edu">pegasus-users@isi.edu</a>&gt;</code> to get help. You can
    also contact us on our <a class="ulink" href="https://pegasus.isi.edu/support" target="_top">support chatroom</a> on HipChat.
    </strong></span></p>
</div>
</div><div class="navfooter">
<hr>
<table width="100%" summary="Navigation footer">
<tr>
<td width="40%" align="left">
<a accesskey="p" href="index.php">Prev</a> </td>
<td width="20%" align="center"> </td>
<td width="40%" align="right"> <a accesskey="n" href="tutorial_started.php">Next</a>
</td>
</tr>
<tr>
<td width="40%" align="left" valign="top">Scientific Workflows through Pegasus WMS at USC HPC Cluster </td>
<td width="20%" align="center"><a accesskey="h" href="index.php">Table of Contents</a></td>
<td width="40%" align="right" valign="top"> 1.2. Getting Started</td>
</tr>
</table>
</div><?php  
            pegasus_footer();
        ?>
