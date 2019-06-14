PEGASUS_HOME=$(shell cd ../.. && pwd)
VERSION=$(shell ../../release-tools/getversion)

XML  = tutorial_ccin2p3.xml

TARGETS = html 

all: $(TARGETS)


html: $(XML) pegasus-wordpress-style.xsl
	mkdir -p ./html/images
	xsltproc --noout --stringparam base.dir ./html/ --xinclude pegasus-wordpress-style.xsl tutorial-book.xml
	cp images/*.png  images/*.jpg ./html/images/
	touch html

upload: html
	rsync -a -v -e ssh html/. pegasus.isi.edu:/srv/pegasus.isi.edu/public_html/tutorial/CC-IN2P3/.

clean:
	rm -rf html 

