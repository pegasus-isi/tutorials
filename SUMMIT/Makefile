PEGASUS_HOME=$(shell cd ../.. && pwd)
VERSION=$(shell ../../release-tools/getversion)

XML  = tutorial_summit.xml

TARGETS = html 

all: $(TARGETS)


html: $(XML) pegasus-wordpress-style.xsl
	mkdir -p ./html/images
	xsltproc --noout --stringparam base.dir ./html/ --xinclude pegasus-wordpress-style.xsl tutorial-book.xml
	cp images/*.png  images/*.jpg ./html/images/
	touch html


clean:
	rm -rf html 

