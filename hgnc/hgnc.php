<?php

/**
Copyright (C) 2013 Alison Callahan, Jose Cruz-Toledo and Michel Dumontier

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
of the Software, and to permit persons to whom the Software is furnished to do
so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

/**
 * An RDF generator for HGNC (http://www.genenames.org/)
 * @version 2.0
 * @author Alison Callahan
 * @author Jose Cruz-Toledo
 * @author Michel Dumontier
*/
// require_once(__DIR__.'/../../php-lib/bio2rdfapi.php');

class HGNCParser extends Bio2RDFizer {
	private $version = 2.0;
	function __construct($argv){
		parent::__construct($argv, "hgnc");
		parent::addParameter('files',true,'all','all','files to process');
		parent::addParameter('download_url',false,null,'ftp://ftp.ebi.ac.uk/pub/databases/genenames/hgnc_complete_set.txt.gz');
		parent::initialize();
	}//constructor

	function Run(){
		$file = "hgnc_complete_set.txt.gz";
		$ldir = parent::getParameterValue('indir');
		$odir = parent::getParameterValue('outdir');
		$rdir = parent::getParameterValue('download_url');
		$lfile = $ldir.$file;
		if(!file_exists($lfile) && parent::getParameterValue('download') == false) {
			trigger_error($lfile." not found. Will attempt to download.", E_USER_NOTICE);
			parent::setParameterValue('download',true);
		}
		//download the hgnc file
		$rfile = null;
		if(parent::getParameterValue('download') == true) {
			$rfile = $rdir;
			echo "downloading $file ... ";
			Utils::DownloadSingle($rfile, $lfile);
		}

		$ofile = $odir."hgnc.".parent::getParameterValue('output_format');
		$gz=false;
		if(strstr(parent::getParameterValue('output_format'), "gz")){$gz = true;}

		parent::setWriteFile($ofile, $gz);
		parent::setReadFile($lfile, true);
		echo "processing $file... ";
		$this->process();
		echo "done!".PHP_EOL;
		//close write file
		parent::getWriteFile()->close();
		echo PHP_EOL;
		// generate the dataset release file
		echo "generating dataset release file... ";
		$dataset_description = '';
		$source_file = (new DataResource($this))
			->setURI($rdir)
			->setTitle('HUGO Gene Nomenclature Committee (HGNC)')
			->setRetrievedDate(date("Y-m-d\TG:i:s\Z", filemtime($lfile)))
			->setFormat('text/tab-separated-value')
			->setFormat('application/zip')
			->setPublisher('http://www.genenames.org/')
			->setHomepage('http://www.genenames.org/data/gdlw_columndef.html')
			->setRights('use')
			->setRights('attribution')
			->setLicense('http://www.genenames.org/about/overview')
			->setDataset(parent::getDatasetURI());
		
		$prefix = parent::getPrefix();
		$bVersion = parent::getParameterValue('bio2rdf_release');
		$date = date("Y-m-d\TG:i:s\Z");
		$output_file = (new DataResource($this))
			->setURI("http://download.bio2rdf.org/release/$bVersion/$prefix")
			->setTitle("Bio2RDF v$bVersion RDF version of $prefix (generated at $date)")
			->setSource($source_file->getURI())
			->setCreator("https://github.com/bio2rdf/bio2rdf-scripts/blob/master/hgnc/hgnc.php")
			->setCreateDate($date)
			->setHomepage("http://download.bio2rdf.org/release/$bVersion/$prefix/$prefix.html")
			->setPublisher("http://bio2rdf.org")
			->setRights("use-share-modify")
			->setRights("restricted-by-source-license")
			->setLicense("http://creativecommons/licenses/by/3.0/")
			->setDataset(parent::getDatasetURI());
		
		parent::getWriteFile()->close();
		echo "done!".PHP_EOL;
		if($gz) $output_file->setFormat("application/gzip");
		if(strstr(parent::getParameterValue('output_format'),"nt")) $output_file->setFormat("application/n-triples");
		else $output_file->setFormat("application/n-quads");
		$dataset_description .= $source_file->toRDF().$output_file->toRDF();
		$this->setWriteFile($odir.$this->getBio2RDFReleaseFile());
		$this->getWriteFile()->write($dataset_description);
		$this->getWriteFile()->close();
		
	}//Run

	function process(){
		$header = $this->GetReadFile()->Read(200000);
		$header_arr = explode("\t", $header);
		$n = 41;
		$c = count($header_arr);
		if ($c != $n)
		{
			echo PHP_EOL;
			print_r($header_arr);
			trigger_error ("Expected $n columns, found $c . please update the script",E_USER_ERROR);
			exit;
		}

		while($l = $this->GetReadFile()->Read(4096)) {
			$fields = explode("\t", $l);
			$id = strtolower($fields[0]);
			$approved_symbol = $fields[1];
			$approved_name = $fields[2];
			$status = $fields[3];
			$locus_type = $fields[4];
			$locus_group = $fields[5];
			$previous_symbols = $fields[6];
			$previous_names = $fields[7];
			$synonyms = $fields[8];
			$name_synonyms = $fields[9];
			$chromosome = $fields[10];
			$date_approved = $fields[11];
			$date_modified = $fields[12];
			$date_symbol_changed = $fields[13];
			$date_name_changed = $fields[14];
			$accession_numbers = $fields[15];
			$enzyme_ids = $fields[16];
			$entrez_gene_id = $fields[17];
			$ensembl_gene_id = $fields[18];
			$mouse_genome_database_id = $fields[19];
			$specialist_database_links = $fields[20];
			$specialist_database_ids = $fields[21];
			$pubmed_ids = $fields[22];
			$refseq_ids = $fields[23];
			$gene_family_tag = $fields[24];
			$gene_family_description = $fields[25];
			$record_type = $fields[26];
			$primary_ids = $fields[27];
			$secondary_ids = $fields [28];
			$ccd_ids = $fields[29];
			$vega_ids = $fields[30];
			$locus_specific_databases = $fields[31];
			$entrez_gene_id_mappeddatasuppliedbyNCBI = $fields[32];
			$omim_id_mappeddatasuppliedbyNCBI = $fields[33];
			$refseq_mappeddatasuppliedbyNCBI = $fields[34];
			$uniprot_id_mappeddatasuppliedbyUniProt = $fields[35];
			$ensembl_id_mappeddatasuppliedbyEnsembl = $fields[36];
			$vega_id_mappeddatasuppliedbyVega = $fields[37];
			$ucsc_id_mappeddatasuppliedbyUCSC = $fields[38];
			$mouse_genome_database_id_mappeddatasuppliedbyMGI = $fields[39];
			$rat_genome_database_id_mappeddatasuppliedbyRGD = $fields[40];

			$id_res = $id;
			$id_label = "Gene Symbol for ".$approved_symbol;
			parent::AddRDF(
				parent::triplify($id_res, "rdf:type", $this->getVoc()."Gene-Symbol").
				parent::describeIndividual($id_res, $id_label, $this->getVoc()."Gene-Symbol").
				parent::describeClass($this->getVoc()."Gene-Symbol", "HGNC Official Gene Symbol")
			);
			if(!empty($approved_symbol)){
				$s = "hgnc.symbol:".$approved_symbol;
				parent::AddRDF(
					parent::triplifyString($id_res, $this->getVoc()."approved-symbol",utf8_encode(htmlspecialchars($approved_symbol))).
					parent::describeProperty($this->getVoc()."approved-symbol", "HGNC approved gene symbol","The official gene symbol that has been approved by the HGNC and is publicly available. Symbols are approved based on specific HGNC nomenclature guidelines. In the HTML results page this ID links to the HGNC Symbol Report for that gene").
					parent::describeIndividual($s, $approved_symbol, parent::getVoc()."Approved-Gene-Symbol").
					parent::describeClass(parent::getVoc()."Approved-Gene-Symbol","Approved Gene Symbol").
					parent::triplify($id_res, parent::getVoc()."has-approved-symbol", $s).
					parent::triplify($s, parent::getVoc()."is-approved-symbol-of", $id_res)
				);
				
			}
			if(!empty($approved_name)){
				parent::AddRDF(
					parent::triplifyString($id_res, $this->getVoc()."approved-name",utf8_encode(htmlspecialchars($approved_name))).
					parent::describeProperty($this->getVoc()."approved-name","HGNC approved name", "The official gene name that has been approved by the HGNC and is publicly available. Names are approved based on specific HGNC nomenclature guidelines.")
				);
			}			
			if(!empty($status)){
				$s = $this->getVoc().str_replace(" ","-",$status);
				parent::AddRDF(
					parent::triplify($id_res, $this->getVoc()."status",$s).
					parent::describeProperty($this->getVoc()."status","HGNC status", "Indicates whether the gene is classified as: Approved - these genes have HGNC-approved gene symbols. Entry withdrawn - these previously approved genes are no longer thought to exist. Symbol withdrawn - a previously approved record that has since been merged into a another record.").
					parent::describeClass($s,$status,$this->getVoc()."Status")
				);
			}			
			if(!empty($locus_id)){
				$locus_res = $this->getRes().$id."_LOCUS";
				parent::AddRDF(
					parent::triplify($id_res, $this->getVoc()."locus", $locus_res).
					parent::triplifyString($locus_res, $this->getVoc()."locus-type",utf8_encode(htmlspecialchars($locus_type))).
					parent::triplifyString($locus_res, $this->getVoc()."locus-group", utf8_encode(htmlspecialchars($locus_group))).
					parent::describeProperty($this->getVoc()."locus-type", "locus type","Specifies the type of locus described by the given entry").
					parent::describeProperty($this->getVoc()."locus-group", "locus group", "Groups locus types together into related sets. Below is a list of groups and the locus types within the group")
				);
			}
			if(!empty($previous_symbols)){
				$previous_symbols = explode(", ", $previous_symbols);
				foreach($previous_symbols as $previous_symbol){
					$previous_symbol_uri = "hgnc.symbol:".$previous_symbol;
					parent::AddRDF(
						parent::describeIndividual($previous_symbol_uri, $previous_symbol, parent::getVoc()."Previous-Symbol").
						parent::describeClass(parent::getVoc()."Previous-Symbol","Previous Symbol").
						parent::triplify($id_res, $this->getVoc()."previous-symbol", $previous_symbol_uri).
						parent::describeProperty($this->getVoc()."previous-symbol", "HGNC previous symbol","Symbols previously approved by the HGNC for this gene")
					);
				}
			}
			if(!empty($previous_names)){
				$previous_names = explode(", ", $previous_names);
				foreach($previous_names as $previous_name){
					$previous_name = str_replace("\"", "", $previous_name);
					parent::AddRDF(
						parent::triplifyString($id_res, $this->getVoc()."previous-name",  utf8_encode(htmlspecialchars($previous_name))).
						parent::describeProperty($this->getVoc()."previous-name", "HGNC previous name","Gene names previously approved by the HGNC for this gene")
					);
				}
			}
			if(!empty($synonyms)){
				$synonyms = explode(", ", $synonyms);
				foreach ($synonyms as $synonym) {
					parent::AddRDF(
						parent::triplifyString($id_res, $this->getVoc()."synonym",  utf8_encode(htmlspecialchars($synonym))).
						parent::describeProperty($this->getVoc()."synonym", "synonym","Other symbols used to refer to this gene")
					);
				}
			}
			if(!empty($name_synonyms)){
				$name_synonyms = explode(", ", $name_synonyms);
				foreach ($name_synonyms as $name_synonym) {
					$name_synonym = str_replace("\"", "", $name_synonym);
					parent::AddRDF(
						parent::triplifyString($id_res, $this->getVoc()."name-synonym",  utf8_encode(htmlspecialchars($name_synonym))).
						parent::describeProperty($this->getVoc()."name-synonym", "name synonym","Other names used to refer to this gene")
					);
				}
			}
			if(!empty($chromosome)){
				parent::AddRDF(
					parent::triplifyString($id_res, $this->getVoc()."chromosome",  utf8_encode(htmlspecialchars($chromosome))).
					parent::describeProperty($this->getVoc()."chromosome", "chromosome", "Indicates the location of the gene or region on the chromosome")
				);
			}
			if(!empty($date_approved)){
				parent::AddRDF(
					parent::triplifyString($id_res, $this->getVoc()."date-approved", $date_approved, "xsd:date").
					parent::describeProperty($this->getVoc()."date-approved", "date approved","Date the gene symbol and name were approved by the HGNC")
				);
			}
			if(!empty($date_modified)){
				parent::AddRDF(
					parent::triplifyString($id_res, $this->getVoc()."date-modified", $date_modified, "xsd:date").
					parent::describeProperty($this->getVoc()."date-modified", "date modified", "the date the entry was modified by the HGNC")
				);
			}
			if(!empty($date_symbol_changed)){
				parent::AddRDF(
					parent::triplifyString($id_res, $this->getVoc()."date-symbol-changed", $date_symbol_changed, "xsd:date").
					parent::describeProperty($this->getVoc()."date-symbol-changed", "date symbol changed","The date the gene symbol was last changed by the HGNC from a previously approved symbol. Many genes receive approved symbols and names which are viewed as temporary (eg C2orf#) or are non-ideal when considered in the light of subsequent information. In the case of individual genes a change to the name (and subsequently the symbol) is only made if the original name is seriously misleading")
				);
			}
			if(!empty($date_name_changed)){
				parent::AddRDF(
					parent::triplifyString($id_res, $this->getVoc()."date-name-changed", $date_name_changed, "xsd:date").
					parent::describeProperty($this->getVoc()."date-name-changed", "date name changed", "The date the gene name was last changed by the HGNC from a previously approved name")
				);
			}
			if(!empty($accession_numbers)){
				$accession_numbers = explode(", ", $accession_numbers);
				foreach ($accession_numbers as $accession_number) {
					parent::AddRDF(
						parent::triplifyString($id_res, $this->getVoc()."accession",  utf8_encode(htmlspecialchars($accession_number))).
						parent::describeProperty($this->getVoc()."accession", "accession number", "Accession numbers for each entry selected by the HGNC")
					);
				}
			}
			if(!empty($enzyme_ids)){
				$enzyme_ids = explode(", ", $enzyme_ids);
				foreach ($enzyme_ids as $enzyme_id) {
					parent::AddRDF(
						parent::triplifyString($id_res, $this->getVoc()."x-ec",  utf8_encode(htmlspecialchars($enzyme_id))).
						parent::describeProperty($this->getVoc()."x-ec","Enzyme Commission (EC) number", "Enzyme entries have Enzyme Commission (EC) numbers associated with them that indicate the hierarchical functional classes to which they belong")
					);
				}
			}
			if(!empty($entrez_gene_id)){
				parent::AddRDF(
					parent::triplify($id_res, $this->getVoc()."x-ncbigene",  "ncbigene:$entrez_gene_id").
					parent::describeProperty($this->getVoc()."x-ncbigene", "NCBI Gene", "NCBI Gene provides curated sequence and descriptive information about genetic loci including official nomenclature, synonyms, sequence accessions, phenotypes, EC numbers, MIM numbers, UniGene clusters, homology, map locations, and related web sites")
				);
			}
			if(!empty($ensembl_gene_id)){
				parent::AddRDF(
					parent::triplify($id_res, $this->getVoc()."x-ensembl", "ensembl:$ensembl_gene_id").
					parent::describeProperty($this->getVoc()."x-ensembl", "Ensembl Gene")
				);
			}

			if(!empty($mouse_genome_database_id)){
				if(strpos($mouse_genome_database_id, "MGI:") !== FALSE){
					$mouse_genome_database_id = substr($mouse_genome_database_id, 4);
					parent::AddRDF(
						parent::triplify($id_res, $this->getVoc()."x-mgi", "mgi:$mouse_genome_database_id").
						parent::describeProperty($this->getVoc()."x-mgi", "MGI entry")
					);
				}
			}
			if(!empty($specialist_database_links)){
				$specialist_database_links = explode(", ", $specialist_database_links);
				foreach ($specialist_database_links as $specialist_database_link) {
					preg_match('/href="(\S+)"/', $specialist_database_link, $matches);
					if(!empty($matches[1])){
						parent::AddRDF(
							parent::QQuadO_URL($id_res, $this->getVoc()."xref",  $matches[1]).
							parent::describeProperty($this->getVoc()."xref", "Specialist database references.")
						);
					}
				}
			}
			if(!empty($pubmed_ids)){
				$pubmed_ids = explode(", ", $pubmed_ids);
				foreach ($pubmed_ids as $pubmed_id) {
					parent::AddRDF(
						parent::triplify($id_res, $this->getVoc()."x-pubmed", "pubmed:".trim($pubmed_id)).
						parent::describeProperty($this->getVoc()."x-pubmed", "NCBI PubMed entry","Identifier that links to published articles relevant to the entry in the NCBI's PubMed database.")
					);
				}
			}
			if(!empty($refseq_ids)){
				$refseq_ids = explode(", ", $refseq_ids);
				foreach ($refseq_ids as $refseq_id) {
					parent::AddRDF(
						parent::triplify($id_res, $this->getVoc()."x-refseq", "refseq:".trim($refseq_id)).
						parent::describeProperty($this->getVoc()."x-refseq", "NCBI Refseq entry","The Reference Sequence (RefSeq) identifier for that entry, provided by the NCBI. As we do not aim to curate all variants of a gene only one selected RefSeq is displayed per gene report. RefSeq aims to provide a comprehensive, integrated, non-redundant set of sequences, including genomic DNA, transcript (RNA), and protein products. RefSeq identifiers are designed to provide a stable reference for gene identification and characterization, mutation analysis, expression studies, polymorphism discovery, and comparative analyses. In the HTML results page this ID links to the RefSeq page for that entry.")
					);
				}
			}
			if(!empty($gene_family_tag)){
				parent::AddRDF(
					parent::triplifyString($id_res, $this->getVoc()."gene-family-tag",  utf8_encode(htmlspecialchars($gene_family_tag))).
					parent::describeProperty($this->getVoc()."gene-family-tag", "Gene Family Tag","Tag used to designate a gene family or group the gene has been assigned to, according to either sequence similarity or information from publications, specialist advisors for that family or other databases. Families/groups may be either structural or functional, therefore a gene may belong to more than one family/group. These tags are used to generate gene family or grouping specific pages at genenames.org and do not necessarily reflect an official nomenclature. Each gene family has an associated gene family tag and gene family description. If a particular gene is a member of more than one gene family, the tags and the descriptions will be shown in the same order.")
				);
			}

			if(!empty($gene_family_description)){
				$gene_family_description = str_replace("\"", "", $gene_family_description);
				parent::AddRDF(
					parent::triplifyString($id_res, $this->getVoc()."gene-family-description",  utf8_encode(htmlspecialchars($gene_family_description))).
					parent::describeProperty($this->getVoc()."gene-family-description", "gene family name","Name given to a particular gene family. The gene family description has an associated gene family tag. Gene families are used to group genes according to either sequence similarity or information from publications, specialist advisors for that family or other databases. Families/groups may be either structural or functional, therefore a gene may belong to more than one family/group.")
				);
			}
			if(!empty($record_type)){
				parent::AddRDF(
					parent::triplifyString($id_res, $this->getVoc()."record-type",  utf8_encode(htmlspecialchars($record_type)))
				);
			}
			if(!empty($primary_ids)){
				$primary_ids = explode(", ", $primary_ids);
				foreach ($primary_ids as $primary_id) {
					parent::AddRDF(
						parent::triplifyString($id_res, $this->getVoc()."primary-id",  utf8_encode(htmlspecialchars($primary_id))).
						parent::describeProperty($this->getVoc()."primary-id", "primary identifier")
					);
				}
			}
			if(!empty($secondary_ids)){
				$secondary_ids = explode(", ", $secondary_ids);
				foreach ($secondary_ids as $secondary_id) {
					parent::AddRDF(
						parent::triplifyString($id_res, $this->getVoc()."secondary-id",  utf8_encode(htmlspecialchars($secondary_id))).
						parent::describeProperty($this->getVoc()."secondary-id", "secondary identifier")
					);
				}
			}
			if(!empty($ccd_ids)){
				$ccd_ids = explode(", ", $ccd_ids);
				foreach ($ccd_ids as $ccd_id) {
					parent::AddRDF(
						parent::triplify($id_res, $this->getVoc()."x-ccds", "ccds:".trim($ccd_id)).
						parent::describeProperty($this->getVoc()."x-ccds", "consensus CDS entry","The Consensus CDS (CCDS) project is a collaborative effort to identify a core set of human and mouse protein coding regions that are consistently annotated and of high quality. The long term goal is to support convergence towards a standard set of gene annotations.")
					);
				}
			}
			if(!empty($vega_ids)){
				$vega_ids = explode(", ", $vega_ids);
				foreach ($vega_ids as $vega_id) {
					parent::AddRDF(
						parent::triplify($id_res, $this->getVoc()."x-vega", "vega:".trim($vega_id)).
						parent::describeProperty($this->getVoc()."x-vega", "VEGA gene entry")
					);
				}
			}
			if(!empty($locus_specific_databases)){
				parent::AddRDF(
					parent::triplifyString($id_res, $this->getVoc()."locus-specific-xref",  utf8_encode(htmlspecialchars($locus_specific_databases))).
					parent::describeProperty($this->getVoc()."locus-specific-xref", "locus specific xref", "This contains a list of links to databases or database entries pertinent to the gene")
				);
			}
			if(!empty($entrez_gene_id_mappeddatasuppliedbyNCBI)){
				$entrez_gene_id_mappeddatasuppliedbyNCBI = explode(", ", $entrez_gene_id_mappeddatasuppliedbyNCBI);
				foreach ($entrez_gene_id_mappeddatasuppliedbyNCBI as $gene_id) {
					if(strstr($gene_id, ":") !== FALSE){
						$a = explode(":", $gene_id);
						$gene_id = $a[1];
					}
					parent::AddRDF(
						parent::triplify($id_res, $this->getVoc()."x-ncbigene", "ncbigene:".trim($gene_id)).
						parent::describeProperty($this->getVoc()."x-ncbigene", "NCBI Gene entry")
					);
								
				}
			}
			if(!empty($omim_id_mappeddatasuppliedbyNCBI)){
				$omim_id_mappeddatasuppliedbyNCBI = explode(", ", $omim_id_mappeddatasuppliedbyNCBI);
				foreach ($omim_id_mappeddatasuppliedbyNCBI as $omim_id) {
					parent::AddRDF(
						parent::triplify($id_res, $this->getVoc()."x-omim", "omim:".trim($omim_id)).
						parent::describeProperty($this->getVoc()."x-omim", "OMIM entry","Identifier provided by Online Mendelian Inheritance in Man (OMIM) at the NCBI. This database is described as a catalog of human genes and genetic disorders containing textual information and links to MEDLINE and sequence records in the Entrez system, and links to additional related resources at NCBI and elsewhere. In the HTML results page this ID links to the OMIM page for that entry.")
					);
				}
			}
			if(!empty($refseq_mappeddatasuppliedbyNCBI)){
				$refseq_mappeddatasuppliedbyNCBI = explode(", ", $refseq_mappeddatasuppliedbyNCBI);
				foreach ($refseq_mappeddatasuppliedbyNCBI as $refseq_id) {
					parent::AddRDF(
						parent::triplify($id_res, $this->getVoc()."x-refseq", "refseq:".trim($refseq_id)).
						parent::describeProperty($this->getVoc()."x-refseq", "NCBI Refseq entry","The Reference Sequence (RefSeq) identifier for that entry, provided by the NCBI. As we do not aim to curate all variants of a gene only one selected RefSeq is displayed per gene report. RefSeq aims to provide a comprehensive, integrated, non-redundant set of sequences, including genomic DNA, transcript (RNA), and protein products. RefSeq identifiers are designed to provide a stable reference for gene identification and characterization, mutation analysis, expression studies, polymorphism discovery, and comparative analyses. In the HTML results page this ID links to the RefSeq page for that entry.")
					);
				}
			}
			if(!empty($uniprot_id_mappeddatasuppliedbyUniProt)){
				$uniprot_id_mappeddatasuppliedbyUniProt = explode(", ", $uniprot_id_mappeddatasuppliedbyUniProt);
				foreach ($uniprot_id_mappeddatasuppliedbyUniProt as $uniprot_id) {
					parent::AddRDF(
						parent::triplify($id_res, $this->getVoc()."x-uniprot", "uniprot:".trim($uniprot_id)).
						parent::describeProperty($this->getVoc()."x-uniprot", "Uniprot entry","The UniProt identifier, provided by the EBI. The UniProt Protein Knowledgebase is described as a curated protein sequence database that provides a high level of annotation, a minimal level of redundancy and high level of integration with other databases. In the HTML results page this ID links to the UniProt page for that entry.")
					);
				}
			}
			if(!empty($ensembl_id_mappeddatasuppliedbyEnsembl)){
				$ensembl_id_mappeddatasuppliedbyEnsembl = explode(", ", $ensembl_id_mappeddatasuppliedbyEnsembl);
				foreach ($ensembl_id_mappeddatasuppliedbyEnsembl as $ensembl_id) {
					parent::AddRDF(
						parent::triplify($id_res, $this->getVoc()."x-ensembl", "ensembl:".trim($refseq_id)).
						parent::describeProperty($this->getVoc()."x-ensembl", "Ensembl entry","The Ensembl ID is derived from the current build of the Ensembl database and provided by the Ensembl team.")
					);
				}
			}

			if(!empty($ucsc_id_mappeddatasuppliedbyVega)){
				$ucsc_id_mappeddatasuppliedbyVega = explode(", ", $ucsc_id_mappeddatasuppliedbyVega);
				foreach ($ucsc_id_mappeddatasuppliedbyVega as $vega_id) {
					parent::AddRDF(
						parent::triplify($id_res, $this->getVoc()."x-vega", "vega:".trim($vega_id)).
						parent::describeProperty($this->getVoc()."x-vega", "Vega entry")
					);
				}
			}
			if(!empty($ucsc_id_mappeddatasuppliedbyUCSC)){
				$ucsc_id_mappeddatasuppliedbyUCSC = explode(", ", $ucsc_id_mappeddatasuppliedbyUCSC);
				foreach ($ucsc_id_mappeddatasuppliedbyUCSC as $ucsc_id) {
					parent::AddRDF(
						parent::triplify($id_res, $this->getVoc()."x-ucsc", "ucsc:".trim($ucsc_id)).
						parent::describeProperty($this->getVoc()."x-ucsc", "UCSC entry")
					);
				}
			}
			if(!empty($mouse_genome_database_id_mappeddatasuppliedbyMGI)){
				$mouse_genome_database_id_mappeddatasuppliedbyMGI = explode(", ", $mouse_genome_database_id_mappeddatasuppliedbyMGI);
				foreach ($mouse_genome_database_id_mappeddatasuppliedbyMGI as $mgi_id) {
					if(strpos($mgi_id, "MGI:") !== FALSE){
						$mgi_id = substr($mgi_id, 4);
					}
					parent::AddRDF(
						parent::triplify($id_res, $this->getVoc()."x-mgi", "mgi:".trim($mgi_id)).
						parent::describeProperty($this->getVoc()."x-mgi", "MGI entry")
					);
				}
			}
			if(!empty($rat_genome_database_id_mappeddatasuppliedbyRGD)){
				$rat_genome_database_id_mappeddatasuppliedbyRGD = explode(", ", trim($rat_genome_database_id_mappeddatasuppliedbyRGD));
				foreach ($rat_genome_database_id_mappeddatasuppliedbyRGD as $rgd_id) {
					$rgd_id = trim($rgd_id);
					if(!empty($rgd_id)){
						parent::AddRDF(
							parent::triplify($id_res, $this->getVoc()."x-rgd",  trim($rgd_id)).
							parent::describeProperty($this->getVoc()."x-rgd", "RGD entry")
						);
					}
				}
			}
			//write RDF to file
			$this->WriteRDFBufferToWriteFile();
		}//while
		
	}//process

}//HGNCParser

?>
