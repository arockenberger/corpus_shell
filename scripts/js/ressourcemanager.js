/* 
 * The MIT License
 *
 * Copyright 2016 OEAW/ACDH.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * @fileOverview Provides a managemant class for search endpoints and indexes {@link module:corpus_shell~ResourceManager} and a default object for this purpose {@link module:corpus_shell~ResourceController}.<br/>
 * Main entry points are {@link module:corpus_shell~ResourceManager#AddResource} and {@link module:corpus_shell~ResourceManager#AddIndex} which are called
 * from {@link module:corpus_shell~doOnDocumentReady}. {@link module:corpus_shell~ResourceManager#GetIndexCache} generates the box that is shown after a click on
 * Show indexes. 
 */

/**
 * @module corpus_shell 
 */

(function (exports, $) {

/**
 * The ResourceController object, an {@link module:corpus_shell~ResourceManager} instance.
 * @global
 */
exports.ResourceController;

/**
 * @classdesc Manages resources that is search endpoints and their possible inexes.
 * @memberof! corpus_shell
 * @constructor 
 */
function ResourceManager()
{
  /**
   * A "map" of all the known Resources.
   * @type {map.<ResourceObject>}
   */
  this.Resources = new Array();

  /**
   * @param {string} resname A resource name.
   * @param {string} restitle A title for that resource
   * @desc Adds the resource the the "map" of resources by the given name and stores the title.
   * @return - 
   */
  this.AddResource = function (resname, restitle)
  {
    this.Resources[resname] = this.GetNewResourceObject(resname, restitle);
  }

  /**
   * @param {string} resname A resource name.
   * @param {string} idxname An index name.
   * @param {boolean} searchable Is the index searchable? (accepts anything JS interprets as boolean)
   * @param {boolean} scanable Is the index scanable? (accepts anything JS interprets as boolean)
   * @param {boolean} sortable Is the index sortable? (accepts anything JS interprets as boolean)
   * @desc Adds an index name and its properties to a resource if it exist.
   * @return -  
   */  
  this.AddIndex = function (resname, idxname, idxtitle, searchable, scanable, sortable, native)
  {
    if (searchable)
        searchable = true;
    else
        searchable = false;
    if (scanable)
        scanable = true;
    else
        scanable = false;
    if (sortable)
        sortable = true;
    else
        sortable = false;
    if (native)
        native = true;
    else
        native = false;
    if (this.Resources[resname] !== undefined)
      this.Resources[resname].Indexes[idxname] = this.GetNewIndexObject(idxname, idxtitle, searchable, scanable, sortable, native);
  }

  /**
   * @typedef {Object} ResourceObject
   * @property  {string} Name Name of the resource.
   * @property {string} Title A intelligable title for the resource.
   * @property {array.<IndexObject>} Indexes
   */
  
  /**
   * @param {string} name Name of the resource.
   * @param {string} title A intelligable title for the resource.
   * @desc Creates a new ResourceObject.
   * @return {ResourceObject} The newly created ResourceObject. 
   */
  this.GetNewResourceObject = function (name, title)
  {
    var newObj = new Object();
    newObj["Name"] = name;
    newObj["Title"] = title;
    newObj["Indexes"] = new Array();

    return newObj;
  }
  /**
   * @typedef {Object} IndexObject
   * @property {string} Name Name of the resource this index belongs to.
   * @property {string} Title Name of this index.
   * @property {boolean} Searchable Is this index is searchable?
   * @property {boolean} Scanable Is this index scannable?
   * @property {boolean} Sortable Is this index sortable?
   */
  
  /**
   * @param {string} name Name of the resource this index belongs to.
   * @param {string} title Name of this index.
   * @param {boolean} searchable Is this index is searchable?
   * @param {boolean} scanable Is this index scannable?
   * @param {boolean} sortable Is this index sortable?
   * @desc Creates a new IndexObject.
   * @return {IndexObject} The newly created IndexObject.
   */
  this.GetNewIndexObject = function (name, title, searchable, scanable, sortable, native)
  {
    var newObj = new Object();
    newObj["Name"] = name;
    newObj["Title"] = title;
    newObj["Searchable"] = searchable;
    newObj["Scanable"] = scanable;
    newObj["Sortable"] = sortable;
    newObj["Native"] = native;

    return newObj;
  }
  
  /**
   * @typedef {Object} LabelValueObject
   * @property {string} label A label, corresponds to {@link IndexObject}.Title
   * @property {string} value A value, corresponds to {@link IndexObject}.Name
   */

  /**
   * @param {string|number} resname Name of a resource or a position in the array.
   * @desc Returns an array of LabelValueObjects for the given resource name
   * if that is found in ResourceManager's resources or else all known Index/Title pairs are returned.
   * @return {array.<LabelValueObject>}
   */
  this.GetLabelValueArray = function (resname)
  {
    var list = new Array();

    if (this.Resources[resname] != undefined)
    {
      var resource = this.Resources[resname];
      for (key in resource.Indexes)
      {
        var index = resource.Indexes[key];
        var ary = new Array();
        ary['label'] = index.Title;
        ary['value'] = index.Name;
        list.push(ary);
      }
    }
    else
    {
      for (reskey in this.Resources)
      {
        var resource = this.Resources[reskey];
        for (key in resource.Indexes)
        {
          var index = resource.Indexes[key];

          //distinct -> add only unique values
          var found = false;

          //check if list contains indexName
          for (var idx=0; idx<list.length; idx++)
          {
            var item = list[idx];
            if (item.value == index.Name)
             found = true;
          }

          //add if list doesn't contain indexName
          if (found == false)
          {
            var ary = new Array();
            ary['label'] = index.Title;
            ary['value'] = index.Name;
            list.push(ary);
          }
        }
      }
    }

    return list;
  }

  /**
   * Delete all stored data recursively.
   * @return -
   */
  this.ClearResources = function ()
  {
    for (reskey in this.Resources)
    {
      var resource = this.Resources[reskey];
      for (key in resource.Indexes)
      {
        delete resource.Indexes[key];
      }

      delete this.Resources[reskey];
    }
  }

  /**
   * Creates an HTML snippet containing in a table in a div all the information about the resources.
   * @return {string} An HTML snippet. 
   */
  this.GetIndexCache = function ()
  {
    var hStr = '<div id="openIndexList" style="height: 130px; overflow: auto;"><table id="indexList">';

    for (reskey in this.Resources)
    {
      var resource = this.Resources[reskey];

      hStr += '<tr><td colspan="4" class="dotted b">' + resource.Title + '</td></tr>';

      var cnt = 0;
      for (key in resource.Indexes)
      {
        hStr += '<tr><td rowspan="2" style="width: 15px;"></td>';
        var idx = resource.Indexes[key];
        hStr += '<td class="dotted" colspan="3">' + idx.Title + ' (' + idx.Name + ')</td></tr>';

        hStr += '<tr><td class="dottedr is' + idx.Searchable + '"';
        if (idx.Searchable === true)
          hStr += ' style="cursor: pointer;" onclick="PanelController.OpenNewSearchPanel(\'' + resource.Name + '\', \'' +  idx.Name + '=\');"';
        hStr += '>search';
        if (idx.Native === true)
            hStr += ' native'
        hStr += '</td>';
        hStr += '<td class="dottedr is' + idx.Scanable + '"'

        if (idx.Scanable == true)
          hStr += ' style="cursor: pointer;" onclick="PanelController.OpenNewScanPanel(\'' + resource.Name + '\', \'' +  idx.Name + '\');"';

        hStr += '>scan</td>';
        hStr += '<td class="dottedr is' + idx.Sortable + '">sort</td></tr>';
        hStr += '<tr><td colspan="3" style="height: 10px;"></td></tr>'
        cnt++;
      }
      if (cnt == 0)
      {
        hStr += '<tr><td style="width: 15px;"></td>';
        hStr += '<td colspan="3">no indexes found</td></tr>';
      }
    }

    return hStr + '</table></div>';
  }
}
exports.ResourceController = new ResourceManager();
})(window, jQuery);