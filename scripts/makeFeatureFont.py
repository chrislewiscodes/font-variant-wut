from lib.UI.spaceCenter.glyphSequenceEditText import splitText
from fbits.toolbox.font import FontTX
tags = ['pnum', 'onum', 'tnum', 'lnum', 'liga', 'dlig', 'hlig', 'case', 'cpsp', 'smcp', 'c2sc', 'sups', 'sinf', 'subs', 'ordn', 'titl', 'swsh', 'cswh', 'aalt', 'calt', 'clig', 'frac', 'afrc', 'numr', 'dnom', 'ornm', 'salt', 'ss01', 'hist', 'zero', 'jalt', 'nalt', 'mgrk', 'pcap', 'c2pc', 'unic', 'ccmp', 'rlig', 'mark', 'mkmk', 'mset', 'fina', 'init', 'isol', 'medi']

src = CurrentFont()
f = NewFont()

for gname in sourceList:
    f.insertGlyph(src[gname])

for i, tag in enumerate(tags):
    gnames = splitText(tag, src.getCharacterMapping())
    sub = substitutions[i]
    slugContents = [gname+'.t' for gname in gnames]
    gname = 'tag_'+tag
    FontTX.setSlug(f, gname, slugContents, decompose=True)
    g = f[gname]
    p = g.getPen()
    p.moveTo((0, f.info.descender-padding))
    p.lineTo((0, f.info.ascender+padding))
    p.lineTo((g.width, f.info.ascender+padding))
    p.lineTo((g.width, f.info.descender-padding))

    p.closePath()

    f.features.text += """
    feature %s {
        sub %s by %s;
        } %s;
    """ %(tag, sub, gname, tag)
    
    print """
    <div style="font-feature-settings: '%s';">%s</div>
    """ %(tag, sub)



for gname in sourceList:
    f.removeGlyph(gname)
for gname in substitutions:
    g = f.newGlyph(gname, '')
    g.width = 0

    
f.info.familyName = 'OT Test Font'
f.info.styleName = 'Regular'
    