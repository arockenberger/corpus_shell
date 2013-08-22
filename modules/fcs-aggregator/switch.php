<?php
  /**
   * Switch script that queries upstream resources and enhances their response
   * if requested
   * 
   * Uses the file named in $fcsConfig if it exists.
   * @uses $fcsConfig
   * @package fcs-aggregator
   */
   
  /**
   * Determines how parameters are checked
   * 
   * If $sruMode is set to "strict" all mandatory params get checked and
   * an error message is returned if one is missing (eg. version param
   * not provided). If it's set to anything else eg. "loose" the script
   * just tries to fulfill the request on a best effort bases.
   * @global string $sruMode 
   */
  $sruMode = "strict";

  /**
   * If there is the $fcsConfig file is loaded.
   * 
   * If this file is loaded the $configName cache
   * variable is available.
   * @global bool $fcsConfigFound
   */
  $fcsConfigFound = false;

  /**
   * Load the common config file
   */
  include "../utils-php/config.php";

  /**
   * Common implementation for needed to return sru compliant error messages
   */
  include "../utils-php/diagnostics.php";

  /**
   * Array (a map) containing default xsl style sheets
   * 
   * The keys for the map are searchRetrieve, scan, explain and default.
   * @global array $globalStyles
   */
  $globalStyles = array ("searchRetrieve" => "", "scan" => "", "explain" => "", "default" => "");

  //import fcs configuration cache
  if (file_exists($fcsConfig))
  {
    include $fcsConfig;
    $fcsConfigFound = true;
  }

  /**
   * Get the node value of the first occurrence of $tagName
   * in the children of $node
   * @param DOMNode $node The Node at which the search is started.
   * @param string $tagName The tag to search for.
   * @return string The value of the tag if found else the empty string "" is returned.
   */
  function GetNodeValue($node, $tagName)
  {
     $list = $node->getElementsByTagName($tagName);
     if ($list->length != 0)
     {
       $value = $list->item(0);
       return $value->nodeValue;
     }
     return "";
  }
  
  /**
   * Get the node value of the first occurrence of a $tagName that has an attribute $attr with the value $attrValue
   * 
   * processes the descendants of $node only
   * @param DOMNode $node The Node at which the search is started.
   * @param string $tagName The tag to search for.
   * @param string $attr The attribute to search for.
   * @param string $attrValue The attribure value to search for.
   * @return string Returns the node value of the first fitting tag with $tagName with $attr which has $attrValue
   */
  function GetNodeValueWithAttribute($node, $tagName, $attr, $attrValue)
  {
     $list = $node->getElementsByTagName($tagName);
     $idx = 0;

     if ($list->length != 0)
     {
       while ($idx < $list->length)
       {
         $value = $list->item($idx);

         if ($value->hasAttributes())
         {
           $attribute = $value->attributes->getNamedItem($attr)->nodeValue;
           if ($attribute == $attrValue)
             return $value->nodeValue;
         }
         $idx++;
       }
     }
     return "";
  }

  /**
   * Get default xsl style sheets
   * 
   * Loads $switchConfig as an XML DOM, uses XPath to fetch styles elements and
   * fills the settings into the $globalStyles array.
   * @uses $switchConfig
   * @uses $globalStyles
   */
  function GetDefaultStyles()
  {
    global $switchConfig;
    global $globalStyles;

    $doc = new DOMDocument;
    $doc->Load($switchConfig);

    $xpath = new DOMXPath($doc);
    $query = '//styles';
    $entries = $xpath->query($query);

    foreach ($entries as $entry)
    {
      $keys = array_keys($globalStyles);
      foreach ($keys as $key)
      {
        $hstr = GetNodeValueWithAttribute($entry, "style", "operation", $key);
        if ($hstr != "")
          $globalStyles[$key] = $hstr;
      }
    }
  }

  /**
   * Opens $switchConfig and searches for an element item with an element name value that
   * equals $context - returns configuration infos for the found node in
   * an array.
   * If context is not found in switch.config $configName is used to look up the information
   * if it exists.
   * @uses $switchConfig
   * @uses $fcsConfigFound
   * @uses $configName
   * @param string $context An internal ID for a resource.
   * @return array|false An array (a map) containing "name" (the internal id), "type" (of the resource), "uri" and "style" (the stylesheet to use with the resource).
   */
  function GetConfig($context)
  {
    global $switchConfig;
    $doc = new DOMDocument;
    $doc->Load($switchConfig);

    $xpath = new DOMXPath($doc);
    $query = '//item';
    $entries = $xpath->query($query);

    foreach ($entries as $entry)
    {
       $name = GetNodeValue($entry, "name");
       if ($name == $context)
       {
         $type = GetNodeValue($entry, "type");
         $uri = GetNodeValue($entry, "uri");
         $style = GetNodeValue($entry, "style");
         return array("name" => $name, "type" => $type, "uri" => $uri, "style" => $style);
       }
    }

    global $fcsConfigFound;

    if ($fcsConfigFound)
    {
      global $configName;

      if (array_key_exists($context, $configName))
      {
        $conf = $configName[$context];
        return array("name" => $conf["name"], "type" => $conf["type"], "uri" => $conf["endPoint"], "style" => $conf["style"]);
      }
    }

    return false;
  }

  /**
   * Return all known resources
   * 
   * Returns every entry from switch.config and also all
   * entries from fcs.resource.config.php.
   * @uses $switchConfig
   * @uses $fcsConfigFound
   * @uses $configName
   * @return array An array that contains for every resource the "name" (the internal id) and the "label" (the human intelligable name).
   */
  function GetCompleteConfig()
  {
    global $switchConfig;
    global $fcsConfigFound;

    $configArray = array();

    //open $switchConfig (switch.config)
    $doc = new DOMDocument;
    $doc->Load($switchConfig);

    //pick all item tags
    $xpath = new DOMXPath($doc);
    $query = '//item';
    $entries = $xpath->query($query);

    //iterate through all item tags
    foreach ($entries as $entry)
    {
       //add name and label to $configArray
       $name = GetNodeValue($entry, "name");
       $label = GetNodeValue($entry, "label");
       $type = GetNodeValue($entry, "type");
       array_push($configArray, array("name" => $name, "label" => $label));

       if (($type == "fcs.resource") && ($fcsConfigFound))
       {
         global $configName;
         foreach ($configName as $conf)
         {
           $subName = $conf["name"];

           $pos = strpos($subName, $name);
           if (($pos !== false) && ($pos == 0))
             //add name and displaytext to $configArray
             array_push($configArray, array("name" => $subName, "label" => $conf["displayText"]));
         }
       }
    }

    return $configArray;
  }

  /**
   * Analog to file_exists() this function tests if a given $url does exist
   * @param string $url A URL to be testes.
   * @return bool If $url is reachable or not.
   */
  function url_exists($url)
  {
      $handle = @fopen($url,'r');
      return ($handle !== false);
  }

  /**
   * Fills the $scanCollectionsTemplate with all configured endpoints
   * 
   * Returns the expanded template to the client.
   * @uses $scanCollectionsTemplate
   * @uses $vlibPath
   * @param string $version A version that is inserted into the template.
   */
  function ReturnScan($version)
  {
    global $scanCollectionsTemplate;
    global $vlibPath;

    require_once $vlibPath;

    header ("content-type: text/xml; charset=UTF-8");

    //instantiate template engine with $scanCollectionsTemplate
    $tmpl = new vlibTemplate($scanCollectionsTemplate);

    $tmpl->setvar('version', $version);

    //get all configured endpoints
    $configArray = GetCompleteConfig();

    //fill array for template loop
    $collection = array();
    foreach ($configArray as $item)
    {
      array_push($collection, array('name' => $item['name'], 'label' => $item['label']));
    }

    $tmpl->setloop('collection', $collection);
    //generate xml from template and return it
    $tmpl->pparse();
  }

  /**
   * Reads the $explainSwitchTemplate and returns it
   * 
   * The file is just read from disk, as this doesn't need to be filled.
   * content-type text/xml and charset UTF-8 are set for the answer.
   * @uses $explainSwitchTemplate
   */
  function ReturnExplain()
  {
    global $explainSwitchTemplate;

    header ("content-type: text/xml; charset=UTF-8");
    readfile($explainSwitchTemplate);
  }

  /**
   * Concats $url with the given $paramName and $paramValue
   * 
   * @param string url The parameter part of the URL. Initially it's assumed to be just ?
   * @param string paramName A parameter name to be added.
   * @param string paramValue A parameter value to be added.
   * @return string A parameter part of a URL. Can be fed to this function again to add another parameter.
   */
  function AddParamToUrl($url, $paramName, $paramValue)
  {
    return $url . ($url == "?" ? "" : "&") . "$paramName=$paramValue";
  }

  /**
   * Concats $url with the given $paramName and $paramValue
   * 
   * Like AddParamToUrl but adds parameter checking.
   * @param string url The parameter part of the URL. Initially it's assumed to be just ?
   * @param string paramName A parameter name to be added.
   * @param string paramValue A parameter value to be added.
   * @return string A parameter part of a URL. Can be fed to this function again to add another parameter.
   */
  function AddParamToUrlIfNotEmpty($url, $paramName, $paramValue)
  {
    if (($paramValue !== false) && ($paramValue != ""))
      return AddParamToUrl($url, $paramName, $paramValue);

    return $url;
  }

  /**
   * Generates the query url including all mandatory and optional params
   * 
   * @uses $operation
   * @uses $query
   * @uses $scanClause
   * @uses $responsePosition
   * @uses $maximumTerms
   * @uses $version
   * @uses $maximumRecords
   * @uses $startRecord
   * @uses $recordPacking
   * @uses $recordSchema
   * @uses $resultSetTTL
   * @uses $stylesheet
   * @uses $extraRequestData
   * @uses $xformat
   * @param string $endPoint The (upstream) endpoint for the query URL
   * @param string $xcontext The x-context for the query URL
   * @param string type If "fcs.resource" or "fcs" x-context is used else ignored.
   * @return string A URL string that can be used to execute the query.
   */
  function GetQueryUrl($endPoint, $xcontext, $type)
  {
    //get params
    global $operation;
    global $query;
    global $scanClause;
    global $responsePosition;

    global $maximumTerms;
    global $version;

    global $maximumRecords;
    global $startRecord;
    global $recordPacking;
    global $recordSchema;
    global $resultSetTTL;

    global $stylesheet;
    global $extraRequestData;

    global $xformat;

    $urlStr = "?";

    if (($type == "fcs.resource") || ($type == "fcs"))
      $urlStr = AddParamToUrl($urlStr, "x-context", $xcontext);

    //mandatory params for all operations
    $urlStr =  AddParamToUrl($urlStr, "operation", $operation);
    $urlStr =  AddParamToUrl($urlStr, "version", $version);

    //optional params for all operations
    $urlStr =  AddParamToUrlIfNotEmpty($urlStr, "stylesheet", $stylesheet);
    $urlStr =  AddParamToUrlIfNotEmpty($urlStr, "extraRequestData", $extraRequestData);

    switch ($operation )
    {
      case "explain":
        //optional
        $urlStr =  AddParamToUrlIfNotEmpty($urlStr, "recordPacking", $recordPacking);
      break;
      case "scan":
        //mandatory
        $urlStr =  AddParamToUrl($urlStr, "scanClause", $scanClause);
        //optional
        $urlStr =  AddParamToUrlIfNotEmpty($urlStr, "responsePosition", $responsePosition);
        $urlStr =  AddParamToUrlIfNotEmpty($urlStr, "maximumTerms", $maximumTerms);
      break;
      case "searchRetrieve":
        //mandatory
        $urlStr =  AddParamToUrl($urlStr, "query", $query);
        //optional
        $urlStr =  AddParamToUrlIfNotEmpty($urlStr, "startRecord", $startRecord);
        $urlStr =  AddParamToUrlIfNotEmpty($urlStr, "maximumRecords", $maximumRecords);
        $urlStr =  AddParamToUrlIfNotEmpty($urlStr, "recordPacking", $recordPacking);
        $urlStr =  AddParamToUrlIfNotEmpty($urlStr, "recordSchema", $recordSchema);
        $urlStr =  AddParamToUrlIfNotEmpty($urlStr, "resultSetTTL", $resultSetTTL);

        //if x-format!=html --> forward x-format (added 2012-05-02 by AB)
        if (stripos($xformat, "html")=== false)
          $urlStr =  AddParamToUrlIfNotEmpty($urlStr, "x-format", $xformat);
      break;
      default:
        //"Unsupported parameter value"
        Diagnostics(6, "operation: '$operation'");
      break;
    }

    return $endPoint . $urlStr;
  }

  /**
   * To speed up local queries the local servername (contained in $localhost) is replaced
   * by the term "127.0.0.1"
   * @uses $localhost
   * @param string $url A URL as input.
   * @return string The input URL with the hostname set to 127.0.0.1 if it matches $localhost.
   */
  function ReplaceLocalHost($url)
  {
    global $localhost;
    return str_replace($localhost, "127.0.0.1",  $url);
  }

  /**
   * Returns the XML DOM representation of the document at the URL passed as parameter
   * 
   * @uses ReplaceLocalHost()
   * @uses url_exists()
   * @param string $url A URL from which the document should be fetched.
   * @return DOMDocument|false Returns either the XML DOM representation or false.
   */
  function GetDomDocument($url)
  {
    $url = ReplaceLocalHost($url);

    if (url_exists($url))
    {
      $xmlDoc = new DOMDocument();
      $xmlDoc->load($url);

      return $xmlDoc;
    }

    return false;
  }

  function ReturnXmlDocument($url, $headerStr)
  {
    $url = ReplaceLocalHost($url);

    if (url_exists($url))
    {
      if ($headerStr != "")
        header ($headerStr);

      readfile($url);
    }
    else
      //"Unsupported context set"
      Diagnostics(15, str_replace("&", "&amp;", $url));
  }

  function GetXslStyle($operation, $configItem)
  {
    global $globalStyles;

    switch ($operation)
    {
      case "explain" :
        if (array_key_exists('explain', $globalStyles))
          $style = $globalStyles['explain'];
        elseif (array_key_exists('default', $globalStyles))
          $style = $globalStyles['default'];
        else
          $style == "";

        return ReplaceLocalHost($style);
      break;
      case "scan" :
        if (array_key_exists('scan', $globalStyles))
          $style = $globalStyles['scan'];
        elseif (array_key_exists('default', $globalStyles))
          $style = $globalStyles['default'];
        else
          $style == "";

        return ReplaceLocalHost($style);
      break;
      case "searchRetrieve" :
        if (array_key_exists('style', $configItem))
          $style = $configItem['style'];
        elseif (array_key_exists('searchRetrieve', $globalStyles))
          $style = $globalStyles['searchRetrieve'];
        elseif (array_key_exists('default', $globalStyles))
          $style = $globalStyles['default'];
        else
          $style == "";

        return ReplaceLocalHost($style);
      break;
      default:
        //"Unsupported parameter value"
        Diagnostics(6, "operation: '$operation'");
      break;
    }
  }

  function GetXslStyleDomDocument($operation, $configItem)
  {
    $xslUrl = GetXslStyle($operation, $configItem);
    return GetDomDocument($xslUrl);
  }

  function ReturnXslT($xmlDoc, $xslDoc, $useParams)
  {
    $proc = new XSLTProcessor();
    $proc->importStylesheet($xslDoc);

    if ($useParams)
    {
      global $xformat;
      global $scriptsUrl;

      $proc->setParameter('', 'format', $xformat);
      $proc->setParameter('', 'scripts_url', $scriptsUrl);
    }

    header("content-type: text/html; charset=UTF-8");
    print $proc->transformToXML($xmlDoc);
  }

  function HandleXFormatCases()
  {
    global $context;
    global $xformat;
    global $operation;

    foreach($context as $item)
    {
      $configItem = GetConfig($item);

      if ($configItem !== false)
      {
        $uri = $configItem["uri"];
        $type = $configItem['type'];

        $fileName = GetQueryUrl($uri, $item, $type);

        if (stripos($xformat, "html")!== false)
        {
          $xmlDoc = GetDomDocument($fileName);
          if ($xmlDoc !== false)
          {
            $xslDoc = GetXslStyleDomDocument($operation, $configItem);
            if ($xslDoc !== false)
              ReturnXslT($xmlDoc, $xslDoc, true);
            else
              //"Unsupported context set"
              Diagnostics(15, str_replace("&", "&amp;", $item));
          }
          else
            //"Unsupported context set"
            Diagnostics(15, str_replace("&", "&amp;", $fileName));
        }
        elseif (stripos($xformat, "xsltproc") !== false)
        {
          $proc = new XSLTProcessor();
          header("content-type: text/plain; charset=UTF-8");
          print "XSLTPROC-testing";
          print "hasExsltSupport: ".$proc->hasExsltSupport;
        }
        elseif (stripos($xformat, "xsl") !== false)
        {
          // this option is more or less only for debugging (to see the xsl used)
          $style = GetXslStyle($operation, $configItem);
          ReturnXmlDocument($style, "content-type: text/xml; charset=UTF-8");
        }
        elseif (stripos($xformat, "img") !== false)
          ReturnXmlDocument($fileName, "content-type: image/jpg");
        else
          ReturnXmlDocument($fileName, "content-type: text/xml; charset=UTF-8");
      }
      else
      {
        //"Unsupported context set"
        Diagnostics(15, str_replace("&", "&amp;", $item));
      }
    }
  }

  //load default xsl style sheets from $switchConfig
  GetDefaultStyles();

  // params SRU
  if (isset($_GET['operation'])) $operation = $_GET["operation"]; else $operation = ($sruMode=="strict") ? false : "explain";
  if (isset($_GET['query'])) $query = trim($_GET['query']); else $query = ($sruMode=="strict") ? false : "";
  if (isset($_GET['scanClause'])) $scanClause = trim($_GET['scanClause']); else $scanClause = ($sruMode=="strict") ? false : "";
  if (isset($_GET['responsePosition'])) $responsePosition = trim($_GET['responsePosition']); else $responsePosition = "";

  if (isset($_GET['maximumTerms'])) $maximumTerms = trim($_GET['maximumTerms']); else $maximumTerms = "10";
  if (isset($_GET['version'])) $version = trim($_GET['version']); else $version = ($sruMode=="strict") ? false : "1.2";

  if (isset($_GET['maximumRecords'])) $maximumRecords = trim($_GET['maximumRecords']); else $maximumRecords = "10";
  if (isset($_GET['startRecord'])) $startRecord = trim($_GET['startRecord']); else $startRecord = "1";
  if (isset($_GET['recordPacking'])) $recordPacking = trim($_GET['recordPacking']); else $recordPacking = "xml";
  if (isset($_GET['recordSchema'])) $recordSchema = trim($_GET['recordSchema']); else $recordSchema = "";

  if (isset($_GET['stylesheet'])) $stylesheet = trim($_GET['stylesheet']); else $stylesheet = "";
  if (isset($_GET['extraRequestData'])) $extraRequestData = trim($_GET['extraRequestData']); else $extraRequestData = "";

  if (isset($_GET['resultSetTTL'])) $resultSetTTL = trim($_GET['resultSetTTL']); else $resultSetTTL = "";

  //additional params - non SRU
  if (isset($_GET['x-context'])) $xcontext = $_GET["x-context"]; else $xcontext = "";
  if (isset($_GET['x-format'])) $xformat = trim($_GET['x-format']); else $xformat = "";

  //split x-context
  $context = explode(",", $xcontext);

  //no operation param provided ==> explain
  if ($operation === false)
    ReturnExplain();
  else
  {
    switch ($operation)
    {
      case "explain" :
        /*
          Params - taken from http://www.loc.gov/standards/sru/specs/explain.html
          Name              type        Description
          operation         Mandatory   The string: 'explain'.
          version           Mandatory   The version of the request, and a statement by the client
                                        that it wants the response to be less than, or preferably
                                        equal to, that version. See Versions.
          recordPacking     Optional    A string to determine how the explain record should be
                                        escaped in the response. Defined values are 'string' and
                                        'xml'. The default is 'xml'. See Records.
          stylesheet        Optional    A URL for a stylesheet. The client requests that the server
                                        simply return this URL in the response. See Stylesheets.
          extraRequestData  Optional    Provides additional information for the server to process.
                                        See Extensions.
        */
          if ($xcontext == "")
            ReturnExplain();
          else
          {
            HandleXFormatCases();
          }
      break;
      case "scan" :
        /*
          Params - taken from http://www.loc.gov/standards/sru/specs/scan.html
          Name              type        Description
          operation         mandatory   The string: 'scan'.
          version           mandatory   The version of the request, and a statement by the client that
                                        it wants the response to be less than, or preferably equal to,
                                        that version. See Versions.
          scanClause        mandatory   The index to be browsed and the start point within it,
                                        expressed as a complete index, relation, term  clause in CQL.
                                        See CQL.
          responsePosition  optional    The position within the list of terms returned where the
                                        client would like the start term to occur. If the position
                                        given is 0, then the term should be immediately before the
                                        first term in the response. If the position given is 1, then
                                        the term should be first in the list, and so forth up to the
                                        number of terms requested plus 1, meaning that the term should
                                        be immediately after the last term in the response, even if
                                        the number of terms returned is less than the number requested.
                                        The range of values is 0 to the number of terms requested plus
                                        1. The default value is 1.
          maximumTerms      optional    The number of terms which the client requests be returned. The
                                        actual number returned may be less than this, for example if
                                        the end of the term list is reached, but may not be more. The
                                        explain record for the database may indicate the maximum
                                        number of terms which the server will return at once. All
                                        positive integers are valid for this parameter. If not
                                        specified, the default is server determined.
          stylesheet        optional    A URL for a stylesheet. The client requests that the server
                                        simply return this URL in the response. See Stylesheets.
          extraRequestData  optional    Provides additional information for the server to process. See
                                        Extensions.
        */

          if ($scanClause === false)
            //"Mandatory parameter not supplied"
            Diagnostics(7, "scanClause");
          elseif ($version === false)
            //"Mandatory parameter not supplied"
            Diagnostics(7, "version");
          elseif ($scanClause == "")
            //"Unsupported parameter value"
            Diagnostics(6, "scanClause: '$scanClause'");
          elseif ($version == "")
            //"Unsupported parameter value"
            Diagnostics(6, "version: '$version'");
          elseif ($version != "1.2")
            //"Unsupported version"
            Diagnostics(5, "version: '$version'");
          else
          {
            if ($xcontext == "")
            {
              if ($scanClause == "fcs.resource")
                //return switch scan result ==> overview
                ReturnScan($version);
              else
                //"Unsupported parameter value"
                Diagnostics(6, "scanClause: '$scanClause'");
            }
            else
            {
              HandleXFormatCases();
            }
          }

      break;
      case "searchRetrieve" :
        /*
          Params - taken from http://www.loc.gov/standards/sru/specs/search-retrieve.html
          Name              type        Description
          operation         mandatory   The string: 'searchRetrieve'.
          version           mandatory   The version of the request, and a statement by the client that
                                        it wants the response to be less than, or preferably equal to,
                                        that version. See Version.
          query             mandatory   Contains a query expressed in CQL to be processed by the
                                        server. See CQL.
          startRecord       optional    The position within the sequence of matched records of the
                                        first record to be returned. The first position in the
                                        sequence is 1. The value supplied MUST be greater than 0. The
                                        default value if not supplied is 1.
          maximumRecords    optional    The number of records requested to be returned. The value must
                                        be 0 or greater. Default value if not supplied is determined
                                        by the server. The server MAY return less than this number of
                                        records, for example if there are fewer matching records than
                                        requested, but MUST NOT return more than this number of records.
          recordPacking     optional    A string to determine how the record should be escaped in the
                                        response. Defined values are 'string' and 'xml'. The default is
                                        'xml'. See Records.
          recordSchema      optional    The schema in which the records MUST be returned. The value is
                                        the URI identifier for the schema or the short name for it
                                        published by the server. The default value if not supplied is
                                        determined by the server. See Record Schemas.
          resultSetTTL      optional    The number of seconds for which the client requests that the
                                        result set created should be maintained. The server MAY choose
                                        not to fulfil this request, and may respond with a different
                                        number of seconds. If resultSetTTL is not supplied then the
                                        server will determine the value. See Result Sets.
          stylesheet        optional    A URL for a stylesheet. The client requests that the server
                                        simply return this URL in the response. See Stylesheets.
          extraRequestData  optional    Provides additional information for the server to process. See
                                        Extensions.
        */

          if ($query === false)
            //"Mandatory parameter not supplied"
            Diagnostics(7, "query");
          elseif ($version === false)
            //"Mandatory parameter not supplied"
            Diagnostics(7, "version");
          elseif ($query == "")
            //"Unsupported parameter value"
            Diagnostics(6, "query: '$query'");
          elseif ($version == "")
            //"Unsupported parameter value"
            Diagnostics(6, "version: '$version'");
          elseif ($version != "1.2")
            //"Unsupported version"
            Diagnostics(5, "version: '$version'");
          else
          {
            HandleXFormatCases();
          }
      break;
      default:
        //"Unsupported parameter value"
        Diagnostics(6, "operation: '$operation'");
      break;
    }
  }
?>