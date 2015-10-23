PEGASUS_HOME=$(shell cd ../.. && pwd)
VERSION=$(shell ../../release-tools/getversion)

XML  = tutorial_isi.xml

TARGETS = html 

all: $(TARGETS)


html: $(XML) pegasus-php-style.xsl
	mkdir -p ./html/images
	xsltproc --noout --stringparam base.dir ./html/ --xinclude pegasus-php-style.xsl tutorial-book.xml
	cp images/*.png  images/*.jpg ./html/images/
	touch html


clean:
	rm -rf html 

