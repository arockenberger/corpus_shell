<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:utils="http://aac.ac.at/content_repository/utils" xmlns:sru="http://www.loc.gov/standards/sru/" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:fcs="http://clarin.eu/fcs/1.0" xmlns:my="myFunctions" version="2.0">
    <!-- 
<purpose> generate a view for a values-list (index scan) </purpose>
<params>
<param name=""></param>
</params>
<history>
	<change on="2012-02-06" type="created" by="vr">from values2view.xsl, from model2view.xsl</change>
		
</history>

<sample >
<sru:scanResponse xmlns:sru="http://www.loc.gov/standards/sru/" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:fcs="http://clarin.eu/fcs/1.0/">
<sru:version>1.2</sru:version>
   <sru:terms path="//div[@type='diary-day']/p/date/substring(xs:string(@value),1,7)">
        <sru:term>
        <sru:value>1903-01</sru:value>
        <sru:numberOfRecords>30</sru:numberOfRecords>
        </sru:term>
        <sru:term>
        <sru:value>1903-02</sru:value>
        <sru:numberOfRecords>28</sru:numberOfRecords>
        </sru:term>
        <sru:term>
        <sru:value>1903-03</sru:value>
        <sru:numberOfRecords>31</sru:numberOfRecords>
        </sru:term>
   </sru:terms>
   <sru:extraResponseData>
        <fcs:countTerms>619</fcs:countTerms>
    </sru:extraResponseData>
    <sru:echoedScanRequest>
        <sru:scanClause>diary-month</sru:scanClause>
        <sru:maximumTerms>100</sru:maximumTerms>
    </sru:echoedScanRequest>        
 <sru:scanResponse>
 
</sample>
-->
    <xsl:import href="commons.xsl"/>


    <!-- <xsl:param name="size_lowerbound">0</xsl:param>
<xsl:param name="max_depth">0</xsl:param>
<xsl:param name="freq_limit">20</xsl:param>
<xsl:param name="show">file</xsl:param> -->
    <xsl:param name="sort">x</xsl:param>
    <!-- s=size|n=name|t=time|x=default -->
    <xsl:param name="name_col_width">50%</xsl:param>

    <!-- <xsl:param name="mode" select="'htmldiv'" />     -->
    <xsl:param name="title" select="concat('scan: ', $index )"/>

    <!--
<xsl:param name="detail_uri_prefix"  select="'?q='"/> 
-->
    <xsl:output method="html"/>
    <xsl:decimal-format name="european" decimal-separator="," grouping-separator="."/>
    <xsl:param name="index" select="/sru:scanResponse/sru:echoedScanRequest/sru:scanClause"/>
    <xsl:template name="continue-root">
        <div class="cmds-ui-block  init-show">
            <xsl:call-template name="header"/>
            <div class="content">
                <xsl:apply-templates select="/sru:scanResponse/sru:terms"/>
            </div>
        </div>
    </xsl:template>
    
    <!-- <sru:extraResponseData>
        <fcs:countTerms>619</fcs:countTerms>
        </sru:extraResponseData>
        <sru:echoedScanRequest>
        <sru:scanClause>diary-month</sru:scanClause>
        <sru:maximumTerms>100</sru:maximumTerms>        
        </sru:echoedScanRequest> -->
    <xsl:template name="header">
        <xsl:variable name="max-value" select="fcs:countTerms"/>
        <xsl:variable name="start-item" select="'TODO:start-item=?'"/>
        <xsl:variable name="maximum-items" select="/sru:scanResponse/sru:echoedScanRequest/sru:scanClause"/>
        
        <!--  <h2>MDRepository Statistics - index values</h2>  -->
        <div class="header">
            <xsl:attribute name="max-value" select="$max-value"/>
            <xsl:attribute name="start-item" select="$start-item"/>
            <xsl:attribute name="maximum-items" select="$maximum-items"/>
            <xsl:value-of select="$title"/>
            <span class="cmd cmd_detail"/>
            <div class="ui-context-dialog">
                <table class="show">
                    <tbody>
                        <tr>
                            <td class="key">
                                <xsl:value-of select="max-value"/>
                            </td>
                            <td class="value">
                                <xsl:value-of select="$max-value"/>
                            </td>
                        </tr>
                        <tr>
                            <td class="key">
                                <xsl:value-of select="start-item"/>
                            </td>
                            <td class="value">
                                <xsl:value-of select="$start-item"/>
                            </td>
                        </tr>
                        <tr>
                            <td class="key">
                                <xsl:value-of select="maximum-items"/>
                            </td>
                            <td class="value">
                                <xsl:value-of select="$maximum-items"/>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </xsl:template>
    
    <!-- 
sample data:        
        <sru:term>
        <sru:value>cartesian</sru:value>
        <sru:numberOfRecords>35645</sru:numberOfRecords>
        <sru:displayTerm>Carthesian</sru:displayTerm>
        <sru:extraTermData></sru:extraTermData>
        </sru:term>
    -->
    <xsl:template match="sru:terms">
<!--        <xsl:variable name="index" select="my:xpath2index(@path)"/>-->
        <table>
            <xsl:apply-templates select="sru:term"/>
        </table>
    </xsl:template>
    <xsl:template match="sru:term">
        <tr>
            <td align="right">
                <xsl:value-of select="sru:numberOfRecords"/>
            </td>
            <td>
                <span class="cmd cmd_columns"/>
                <a class="value-caller" href="{utils:formURL('searchRetrieve', $format, concat($index, '%3D%22', sru:value, '%22'))}" target="_blank">
                    <xsl:value-of select="(sru:displayTerm, sru:value)[1]"/>
                </a>
            </td>
        </tr>
    </xsl:template>
    <xsl:template name="callback-header">
        <style type="text/css">
            #modeltree {
                margin-left : 10px;
                border : 1px solid #9999aa;
                border-collapse : collapse;
            }
            .number {
                text-align : right;
            }
            td {
                border-bottom : 1px solid #9999aa;
                padding : 1px 4px;
            }
            .treecol {
                padding-left : 1.5em;
            }
            table thead {
                background : #ccccff;
                font-size : 0.9em;
            }
            table thead tr th {
                border : 1px solid #9999aa;
                font-weight : normal;
                text-align : left;
                padding : 1px 4px;
            }</style>
        <script type="text/javascript">
		$(function(){
			$("#modeltree").treeTable({initialState:"expanded"});
			addPaging($('.cmds-ui-block'));
			
			
		});
	</script>
    </xsl:template>
</xsl:stylesheet>