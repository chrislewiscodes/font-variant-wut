
from lib.UI.spaceCenter.glyphSequenceEditText import splitText
from fbits.constants import Constants as C
from fbits.toolbox.font import FontTX

def setSlug(slugName, contents):
    g = f.newGlyph(slugName)
    xoffset = 0
    for gname in contents:
        g.appendComponent(gname, offset=(xoffset, 0))
        xoffset += f[gname].width

f = CurrentFont()
blockfont = AllFonts()[1]

tags = ['c2sc', 'dist', 'subs', 'cswh', 'lnum', 'hwid', 'jp90', 'cjct', 'rkrf', 'afrc', 'ss11', 'ss05', 'unic', 'fina', 'mgrk', 'ss02', 'nlck', 'liga', 'hkna', 'ss12', 'abvs', 'ss15', 'pref', 'calt', 'nukt', 'mark', 'init', 'pstf', 'abvm', 'nalt', 'vkna', 'opbd', 'rand', 'abvf', 'jp04', 'ltra', 'c2pc', 'halt', 'trad', 'vert', 'fin3', 'fin2', 'blws', 'falt', 'ornm', 'vkrn', 'blwm', 'vhal', 'half', 'dlig', 'blwf', 'twid', 'ruby', 'haln', 'case', 'ss20', 'smpl', 'fwid', 'tnam', 'onum', 'sups', 'rtbd', 'expt', 'kern', 'pwid', 'pres', 'rlig', 'pkna', 'vrt2', 'tnum', 'titl', 'curs', 'vatu', 'hngl', 'swsh', 'smcp', 'ss09', 'palt', 'locl', 'clig', 'zero', 'ss08', 'ss07', 'ss06', 'pnum', 'ss04', 'mset', 'cpsp', 'ss03', 'ljmo', 'ss01', 'dnom', 'ordn', 'jalt', 'tjmo', 'hojo', 'psts', 'hlig', 'medi', 'rphf', 'vpal', 'valt', 'hist', 'vjmo', 'akhn', 'size', 'jp83', 'frac', 'numr', 'cpct', 'ital', 'isol', 'aalt', 'ss19', 'ss18', 'cfar', 'mkmk', 'sinf', 'ss10', 'ss13', 'ltrm', 'pcap', 'ss14', 'ss17', 'ss16', 'rtla', 'jp78', 'lfbd', 'ccmp', 'med2', 'rtlm', 'qwid', 'salt', 'rclt', 'asdf']
tags.sort()
for tag in tags:
    tag_gname = 'tag_'+tag
    if tag_gname not in f:
        gnames = splitText(tag, f.getCharacterMapping())
        slugContents = [gname+'.t' for gname in gnames]
        setSlug(f, tag_gname, slugContents)
        f.features.text += """   
	feature %s {
		sub  %s' by %s;
} %s;
""" %(tag, "' ".join(gnames), tag_gname, tag)
        blockfont.features.text += """
	feature %s {
		sub  %s  by tag;
} %s;
        """ %(tag, "' ".join(gnames), tag)
        