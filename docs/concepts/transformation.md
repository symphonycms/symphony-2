---
id: xslt-transformation
title: XSLT Transformation
layout: docs
---

#Concepts: XSLT Transformation

##Overview

An XSLT transformation is the process of applying XSL templates to a source XML document, producing some output (often but not always another XML document).  An XSLT transformation involves 3 elements:

* A source XML document
* An XSL template
* Optionally, additional parameters for use in the XSLT, added at the time of transformation


##Usage

In Symphony, each page represents an XSLT transformation, and each page configuration defines the XSLT, source XML, and parameters that will be used. 

The ___XSL templates___ of a Symphony page xslt transformation are determined by an XSLT document associated with the page. You can edit this document though the Symphony admin page editor, or through your server filesystem. Symphony page XSLT files are located in /workspace/pages.

The ___source XML___ for a Symphony page transformation is primarily determined by the datasources that have been associated with the page,  Events can also supply source XML for a page transformation. Together, all the datasources and events that are assigned to a page make up the source XML document for a Symphony page transformation.

___Additional XSLT parameters___ for a page transformation are supplied for every page. These include some default parameters (such as the current page and date) as well as the output of datasources that have been configured for ‘parameter output”. In Symphony, these additional parameters are referred to as the parameter pool.