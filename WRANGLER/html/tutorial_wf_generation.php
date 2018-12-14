<?php  
            require('/srv/new-pegasus.isi.edu/includes/common.php'); 
            pegasus_header("1.5. Generating the Workflow");
        ?><div class="breadcrumbs">
<span class="breadcrumb-link"><a href="index.php">Scientific Workflows through Pegasus WMS at USC HPC Cluster</a></span> &gt; <span class="breadcrumb-link"><a href="tutorial.php">Tutorial</a></span> &gt; <span class="breadcrumb-node">Generating the Workflow</span>
</div><hr><div class="section">
<div class="titlepage"><div><div><h2 class="title" style="clear: both">
<a name="tutorial_wf_generation"></a>1.5. Generating the Workflow</h2></div></div></div>
<p>The example that you ran earlier already had the workflow
    description (split.dax) generated. Pegasus reads workflow descriptions
    from DAX files. The term "DAX" is short for "Directed Acyclic Graph in
    XML". DAX is an XML file format that has syntax for expressing jobs,
    arguments, files, and dependencies. We now will be creating the split
    workflow that we just ran using the Pegasus provided DAX API:</p>
<div class="figure">
<a name="idm523395039968"></a><p class="title"><b>Figure 1.8. Split Workflow</b></p>
<div class="figure-contents"><div class="mediaobject" align="center"><img src="images/tutorial-split-wf.jpg" align="middle" width="378" alt="Split Workflow"></div></div>
</div>
<br class="figure-break"><p>In this diagram, the ovals represent computational jobs, the
    dog-eared squares are files, and the arrows are dependencies.</p>
<p>In order to create a DAX it is necessary to write code for a DAX
    generator. Pegasus comes with Perl, Java, and Python libraries for writing
    DAX generators. In this tutorial we will show how to use the Python
    library.</p>
<p>The DAX generator for the split workflow is in the file
    <code class="filename">daxgen.py</code>. Look at the file by typing:</p>
<pre class="programlisting">$ <span class="bold"><strong>more daxgen.py</strong></span>
...</pre>
<div class="tip" style="margin-left: 0.5in; margin-right: 0.5in;">
<h3 class="title">Tip</h3>
<p>We will be using the <code class="literal">more</code> command to inspect
      several files in this tutorial. <code class="literal">more</code> is a pager
      application, meaning that it splits text files into pages and displays
      the pages one at a time. You can view the next page of a file by
      pressing the spacebar. Type 'h' to get help on using
      <code class="literal">more</code>. When you are done, you can type 'q' to close
      the file.</p>
</div>
<p>The code has 3 main sections:</p>
<div class="orderedlist"><ol class="orderedlist" type="1">
<li class="listitem">
<p>A new ADAG object is created. This is the main object to which
        jobs and dependencies are added.</p>
<pre class="programlisting"># Create a abstract dag
dax = ADAG("split")
...
</pre>
</li>
<li class="listitem">
<p>Jobs and files are added. The 5 jobs in the diagram above are
        added and 9 files are referenced. Arguments are defined using strings
        and File objects. The input and output files are defined for each job.
        This is an important step, as it allows Pegasus to track the files,
        and stage the data if necessary. Workflow outputs are tagged with
        "transfer=true".</p>
<pre class="programlisting"># the split job that splits the webpage into smaller chunks
webpage = File("pegasus.html")

split = Job("split")
split.addArguments("-l","100","-a","1",webpage,"part.")
split.uses(webpage, link=Link.INPUT)
dax.addJob(split)

...
</pre>
</li>
<li class="listitem">
<p>Dependencies are added. These are shown as arrows in the diagram
        above. They define the parent/child relationships between the jobs.
        When the workflow is executing, the order in which the jobs will be
        run is determined by the dependencies between them.</p>
<pre class="programlisting"># Add control-flow dependencies
dax.depends(wc, split)
</pre>
</li>
</ol></div>
<p>Generate a DAX file named <code class="filename">split.dax</code> by
    typing:</p>
<pre class="programlisting">$ <span class="bold"><strong>./generate_dax.sh </strong></span>split.dax
Generated dax split.dax</pre>
<p>The <code class="filename">split.dax</code> file should contain an XML
    representation of the split workflow. You can inspect it by typing:</p>
<pre class="programlisting">$ <span class="bold"><strong>more split.dax</strong></span>
...</pre>
</div><div class="navfooter">
<hr>
<table width="100%" summary="Navigation footer">
<tr>
<td width="40%" align="left">
<a accesskey="p" href="tutorial_submitting_wf.php">Prev</a> </td>
<td width="20%" align="center"><a accesskey="u" href="tutorial.php">Up</a></td>
<td width="40%" align="right"> <a accesskey="n" href="tutorial_catalogs.php">Next</a>
</td>
</tr>
<tr>
<td width="40%" align="left" valign="top">1.4. Submitting an Example Workflow </td>
<td width="20%" align="center"><a accesskey="h" href="index.php">Table of Contents</a></td>
<td width="40%" align="right" valign="top"> 1.6. Information Catalogs</td>
</tr>
</table>
</div><?php  
            pegasus_footer();
        ?>
