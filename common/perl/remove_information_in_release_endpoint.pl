#!/usr/bin/perl
# dc:title       remove_information_in_release_endpoint.pl
# dc:creator     francoisbelleau at yahoo.ca and manolin at gmail.com
# dc:modified    2009-07-28
# dc:description Do a checkpoint on a specified endpoint
 
# -------------------------------------------------------------------------------
# Bio2RDF is a creation Francois Belleau, Marc-Alexandre Nolin and the Bio2RDF community.
# The SPARQL end points are hosted by the Centre de Recherche du CHUL de Quebec.
# This program is release under the GPL v2 licence. The term of this licence are #specified at http://www.gnu.org/copyleft/gpl.html.
#
# You can contact the Bio2RDF team at bio2rdf@gmail.com
# Visit our blog at http://bio2rdf.blogspot.com/
# Visit the main application at http://bio2rdf.org
# This open source project is hosted at http://sourceforge.net/projects/bio2rdf/
# -------------------------------------------------------------------------------

# perl remove_information_in_release_endpoint.pl <graph>

$graph = shift;

print system("isql 11139 -P releasebio2rdf verbose=on banner=off prompt=off echo=ON errors=stdout exec=\"sparql clear graph <http://bio2rdf.org/release_record:$graph>; \""); 
