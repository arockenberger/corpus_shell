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
 * @fileOverview Configuration of available search contexts, downloaded from corpus3.aac.ac.at.
 * last updated 2012-07-26
 */

/**
 * @module corpus_shell 
 */

/**
 * @typedef {Object} SearchConfigItem
 * @property {string} x-context internal name
 * @property {string} DisplayText a user readable name (eg. for buttons)
 */

/**
 * An array of maps which provides all possible search contexts (['x-context'] => internal name) and
 * their respective human readable description (['DisplayText'] => a user readable name)
 * @type {array.<SearchConfigItem>}
 */
var SearchConfig = new Array();
SearchConfig[0] = new Array();
SearchConfig[0]['x-context'] = 'arz_eng_006';
SearchConfig[0]['DisplayText'] = 'VICAV dictionary Cairo-dialect';

SearchConfig[1] = new Array();
SearchConfig[1]['x-context'] = 'apc_eng_002';
SearchConfig[1]['DisplayText'] = 'VICAV dictionary Damascus-dialect';

SearchConfig[2] = new Array();
SearchConfig[2]['x-context'] = 'deu_wikt_002';
SearchConfig[2]['DisplayText'] = 'Wiktionary';

SearchConfig[3] = new Array();
SearchConfig[3]['x-context'] = 'clarin.at:icltt:ddc';
SearchConfig[3]['DisplayText'] = 'DDC on Corpus3';

SearchConfig[4] = new Array();
SearchConfig[4]['x-context'] = 'clarin.at:icltt:ddc:traum_deu';
SearchConfig[4]['DisplayText'] = ' -> Freud: Die Traumdeutung, German';

SearchConfig[5] = new Array();
SearchConfig[5]['x-context'] = 'clarin.at:icltt:ddc:traum_russ';
SearchConfig[5]['DisplayText'] = ' -> Freud: Die Traumdeutung, Russian';

SearchConfig[6] = new Array();
SearchConfig[6]['x-context'] = 'clarin.at:icltt:ddc:fackel';
SearchConfig[6]['DisplayText'] = ' -> Die Fackel';

SearchConfig[7] = new Array();
SearchConfig[7]['x-context'] = 'clarin.at:icltt:ddc:barock';
SearchConfig[7]['DisplayText'] = ' -> Barocktexte';

SearchConfig[8] = new Array();
SearchConfig[8]['x-context'] = 'clarin.at:icltt:ddc06:c4';
SearchConfig[8]['DisplayText'] = ' -> C4';

SearchConfig[9] = new Array();
SearchConfig[9]['x-context'] = 'clarin.at:icltt:ddc06:c4_vienna';
SearchConfig[9]['DisplayText'] = ' -> C4 Vienna';

SearchConfig[10] = new Array();
SearchConfig[10]['x-context'] = 'sru-gutenberg';
SearchConfig[10]['DisplayText'] = 'Gutenberg Metadata';

SearchConfig[11] = new Array();
SearchConfig[11]['x-context'] = 'vicav-profile';
SearchConfig[11]['DisplayText'] = 'VICAV Profile';

SearchConfig[12] = new Array();
SearchConfig[12]['x-context'] = 'vicav-bib';
SearchConfig[12]['DisplayText'] = 'VICAV Bibliography';

SearchConfig[13] = new Array();
SearchConfig[13]['x-context'] = 'vicav-text';
SearchConfig[13]['DisplayText'] = 'VICAV Sampletexte';

SearchConfig[14] = new Array();
SearchConfig[14]['x-context'] = 'ids-goethe';
SearchConfig[14]['DisplayText'] = 'IDS Goethe';

SearchConfig[15] = new Array();
SearchConfig[15]['x-context'] = 'arz_eng_006_newStyle';
SearchConfig[15]['DisplayText'] = 'VICAV dictionary Cairo-dialect newStyle';

SearchConfig[16] = new Array();
SearchConfig[16]['x-context'] = 'clarin.at:icltt:cr';
SearchConfig[16]['DisplayText'] = 'ICLTT Content Repository';

SearchConfig[17] = new Array();
SearchConfig[17]['x-context'] = 'clarin.at:icltt:cr:stb';
SearchConfig[17]['DisplayText'] = ' -> Schnitzler Tagebuch';

SearchConfig[18] = new Array();
SearchConfig[18]['x-context'] = 'clarin.at:icltt:cr:barock';
SearchConfig[18]['DisplayText'] = ' -> Barock Texte';

SearchConfig[19] = new Array();
SearchConfig[19]['x-context'] = 'clarin.at:icltt:cr:aac-names';
SearchConfig[19]['DisplayText'] = ' -> Namensdatenbank';

SearchConfig[20] = new Array();
SearchConfig[20]['x-context'] = 'clarin-d:digilib';
SearchConfig[20]['DisplayText'] = 'TextGrid Digital Library';

SearchConfig[21] = new Array();
SearchConfig[21]['x-context'] = 'clarin-d:tueba-ddc';
SearchConfig[21]['DisplayText'] = 'Tuebingen';

