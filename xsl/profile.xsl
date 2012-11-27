<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:html="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/xhtml" exclude-result-prefixes="html">

    <xsl:output method="html"/>

    <xsl:template match="/">
      <div class="profiletext">

         <xsl:if test="//div[@type='positioning']">
            <xsl:for-each select="//div[@type='positioning']/p/ref">
               <img style="border:4px solid rgb(212,212,212); width:200px; float:right; margin: 10px 4px 10px 10px;">
                  <xsl:attribute name="src">images/<xsl:value-of select="@target"/></xsl:attribute>
               </img>
            </xsl:for-each>
        </xsl:if>

         <h3><xsl:value-of select="//text/body/head/name[@xml:lang='eng']"/>
            <xsl:if test="//fileDesc/author">
               <span style="font-size: 6pt;font-weight: normal;"> (Contributed by <xsl:value-of select="//fileDesc/author"/>)</span>
            </xsl:if>
         </h3>

         <xsl:for-each select="//text/body/head/name">
            <div style="margin-left:30px">
            <xsl:if test="position()&gt;1">
               ‒  <xsl:value-of select="."/>
             </xsl:if>
             </div>
         </xsl:for-each>

         <br/>

        <h4>Typology</h4>
           <xsl:apply-templates select="//div[@type='typology']"/>
        <br/>

        <h4>Overview</h4>
           <xsl:apply-templates select="//div[@type='researchHistory']"/>
        <br/>

         <xsl:if test="//div[@type='grammars']">
           <h4>Grammars</h4>
           <xsl:apply-templates select="//div[@type='grammars']"/>
           <br/>
        </xsl:if>

         <xsl:if test="//div[@type='dictionaries']">
           <h4>Dictionaries</h4>
           <xsl:apply-templates select="//div[@type='dictionaries']"/>
           <br/>
        </xsl:if>

         <xsl:if test="//div[@type='textBooks']">
           <h4>Textbooks</h4>
           <xsl:apply-templates select="//div[@type='textBooks']"/>
           <br/>
        </xsl:if>

         <xsl:if test="//div[@type='audioData']">
           <h4>Audio data</h4>
           <xsl:apply-templates select="//div[@type='audioData']"/>
           <br/>
        </xsl:if>

         <xsl:if test="//div[@type='sampleText']">
           <h4>Annotated sample texts</h4>
            Basic phrases <a><xsl:attribute name="href">#URL#sampletext=<xsl:value-of select="//div[@type='sampleText']/ptr/@target"/></xsl:attribute><xsl:text>→ </xsl:text></a>
            <br/>
        </xsl:if>

         <xsl:if test="//div[@type='dictionary']">
           <h4>Dictionary</h4>
           <xsl:value-of select="//div[@type='dictionary']"/> <a><xsl:attribute name="href">#URL#dict=<xsl:value-of select="//div[@type='dictionary']/ptr/@target"/></xsl:attribute><xsl:text>→ </xsl:text></a>
            <br/>
        </xsl:if>

         <xsl:if test="//div[@type='bibliography']">
           <h4>Bibliography</h4>
           <xsl:value-of select="//div[@type='bibliography']"/> <a><xsl:attribute name="href">#URL#biblio=<xsl:value-of select="//div[@type='bibliography']/ptr/@target"/></xsl:attribute><xsl:text>→ </xsl:text></a>
            <br/>
        </xsl:if>

        <xsl:if test="//fileDesc/author">
            <br/>
           <p style="text-align: right; padding-right: 20px">(Contributed by <xsl:value-of select="//fileDesc/author"/>)</p>
        </xsl:if>
      </div>
    </xsl:template>

    <xsl:template match="div">
      <div><xsl:apply-templates/></div>
    </xsl:template>

    <xsl:template match="p">
      <p><xsl:apply-templates/></p>
    </xsl:template>

    <xsl:template match="ptrr">
      <a>
        <xsl:attribute name="href">#URL#sampletext=<xsl:value-of select="@target"/></xsl:attribute>
        <xsl:text>→ Basic phrases</xsl:text>
      </a>
    </xsl:template>


    <xsl:template match="teiHeader"/>


</xsl:stylesheet>