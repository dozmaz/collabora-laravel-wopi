import uno
from com.sun.star.text import TableColumnSeparator
from com.sun.star.text import ColumnSeparatorStyle
from com.sun.star.awt.FontWeight import BOLD as FW_BOLD
from com.sun.star.awt.FontWeight import NORMAL as FW_NORMAL
from com.sun.star.table import BorderLineStyle as cStyle
from com.sun.star.style.ParagraphAdjust import RIGHT
from com.sun.star.style.ParagraphAdjust import BLOCK
from com.sun.star.style.ParagraphAdjust import CENTER
from scriptforge import CreateScriptService

def insertTextIntoCell( table, cellName, text, text2, borde ):
    FormatCell(table,cellName,borde)
    tableText = table.getCellByName(cellName)

    #tableText.setString(text)
    oText = tableText.getText()
    oCursor = tableText.createTextCursor()
    oCursor.setPropertyValue("CharWeight",FW_NORMAL)
    oText.insertString(oCursor, text, False )
    if text2 != "":
      oCursor.setPropertyValue("CharWeight",FW_BOLD)
      oText.insertString(oCursor, "\n"+text2, False )

def FormatCell(table,cellName,borde):
    bas = CreateScriptService("Basic")
    line_format = uno.createUnoStruct("com.sun.star.table.BorderLine2")
    line_format.LineStyle = cStyle.DOTTED
    line_format.LineWidth = 0
    line_format.Color = bas.RGB(255, 255, 255)

    line_format2 = uno.createUnoStruct("com.sun.star.table.BorderLine2")
    line_format2.LineStyle = cStyle.SOLID
    line_format2.LineWidth = 50
    line_format2.Color = bas.RGB(0, 0, 0)

    tableText = table.getCellByName(cellName)
    tableText.setPropertyValue("TopBorder", line_format)
    tableText.setPropertyValue("RightBorder", line_format)
    tableText.setPropertyValue("LeftBorder", line_format)
    if borde == 1:
      tableText.setPropertyValue("BottomBorder", line_format2)
    else:
      tableText.setPropertyValue("BottomBorder", line_format)

def mergeCell( oCursor, cellName, rightCount ):
    oCursor.gotoCellByName(cellName, False)
    oCursor.goRight(rightCount, True)
    oCursor.mergeRange()

def InsertText(a, acargo, via, viacargo,de,decargo,referencia,fecha,tipo,cite,hojaruta):
    """Inserts the argument string into the current document.
    If there is a selection, the selection is replaced by it.
    """

    # Get the doc from the scripting context which is made available to
    # all scripts.
    desktop = XSCRIPTCONTEXT.getDesktop()
    model = desktop.getCurrentComponent()

    # Check whether there's already an opened document.
    if not hasattr(model, "Text"):
        return

    # The context variable is of type XScriptContext and is available to
    # all BeanShell scripts executed by the Script Framework
    oDoc = XSCRIPTCONTEXT.getDocument()

    oText = oDoc.getText()

 # The writer controller impl supports the css.view.XSelectionSupplier
    # interface.
    xSelectionSupplier = oDoc.getCurrentController()

    # See section 7.5.1 of developers' guide
    xIndexAccess = xSelectionSupplier.getSelection()
    count = xIndexAccess.getCount()

    sTableName = "TablaCorrespondencia"

    oTables = oDoc.TextTables
    if oTables.hasByName(sTableName):
       #oTable2 = oTables.getByIndex(0)
       oTable2 = oTables.getByName(sTableName)
       oText.Text.removeTextContent(oTable2)
       cursor = oText.createTextCursor()
       oEnum = oText.Text.createEnumeration()
       while oEnum.hasMoreElements():
           oPar = oEnum.nextElement()
           if oPar.supportsService("com.sun.star.text.Paragraph"):
              oText.Text.removeTextContent(oPar)
              break

       oStyle = oDoc.StyleFamilies.getByName("PageStyles").getByName("Standard")
       oHtext = oStyle.HeaderText
       oEnum = oHtext.Text.createEnumeration()
       while oEnum.hasMoreElements():
           oPar = oEnum.nextElement()
           if oPar.supportsService("com.sun.star.text.TextGraphicObject"):
              oText.Text.removeTextContent(oPar)

    oTable = oDoc.createInstance("com.sun.star.text.TextTable")
    oTable.setName(sTableName)
    filas = 6
    if via == "" :
      filas = 5

    oTable.initialize(filas, 8)

    oText.insertString(oText.getStart(), "", 0)

    if tipo != "CARTA EXTERNA" :
      cursor = oText.createTextCursor()
      cursor.setPropertyValue("CharHeight",14.0)
      cursor.setPropertyValue("CharWeight", FW_BOLD)
      cursor.setPropertyValue("CharFontName", "Arial")

      oText.insertString(cursor, tipo, 0)
      if tipo == "CIRCULAR" or tipo == "MEMORANDUM" or tipo == "INSTRUCTIVO":
        oText.insertControlCharacter( cursor, 0, False )
        cursor.setPropertyValue("CharHeight",13.0)
        cursor.setPropertyValue("CharWeight", FW_NORMAL)
        oText.insertString(cursor, "Cite: ", False)
        cursor.setPropertyValue("CharWeight", FW_BOLD)
        oText.insertString(cursor, cite, False)
        oEnum = oText.Text.createEnumeration()
        parrafo = 0
        while oEnum.hasMoreElements():
             oPar = oEnum.nextElement()
             if oPar.supportsService("com.sun.star.text.Paragraph"):
                parrafo = parrafo+1
                if parrafo == 1:
                  oPar.ParaAdjust= CENTER
                elif parrafo == 2:
                  oPar.ParaAdjust= RIGHT
                else:
                  oPar.ParaAdjust= BLOCK
                  break
        oText.insertControlCharacter( cursor, 0, False )
      else:
        oText.insertString(cursor, "\n"+cite, 0)
      oText.insertControlCharacter( cursor, 0, False )

      oText.insertTextContent(cursor, oTable, 0)

      # to set formats using style is better
      oRange = oTable.getCellRangeByName("A1:G"+str(filas))
      oRange.CharHeight = 11.0
      oRange.CharWeight = FW_NORMAL
      oRange.CharFontName = "Arial"

      insertTextIntoCell(oTable,"A1","","",0)
      insertTextIntoCell(oTable,"B1","","",0)
      insertTextIntoCell(oTable,"A2","","",0)
      insertTextIntoCell(oTable,"A3","","",0)
      insertTextIntoCell(oTable,"A4","","",0)

      insertTextIntoCell(oTable,"B2","A:","",0)
      oCursor = oTable.createCursorByCellName("C2")
      insertTextIntoCell(oTable,"C2",a,acargo,0)
      if via == "" :
        insertTextIntoCell(oTable,"B3","DE:","",0)
        insertTextIntoCell(oTable,"C3",de,decargo,0)

        insertTextIntoCell(oTable,"B4","REF.:","",0)
        insertTextIntoCell(oTable,"C4",referencia,"",0)

        insertTextIntoCell(oTable,"A5","","",1)
        insertTextIntoCell(oTable,"B5","FECHA:","",1)
        insertTextIntoCell(oTable,"C5",fecha,"",1)
        insertTextIntoCell(oTable,"G5",hojaruta,"",1)

        mergeCell(oCursor,"C2", 5)
        mergeCell(oCursor,"C3", 5)
        mergeCell(oCursor,"C4", 5)
        mergeCell(oCursor,"G5", 1)
        mergeCell(oCursor,"C5", 3)

        FormatCell(oTable,"C2",0)
        FormatCell(oTable,"C3",0)
        FormatCell(oTable,"C4",0)
        FormatCell(oTable,"C5",1)
        FormatCell(oTable,"D5",1)
      else:
        insertTextIntoCell(oTable,"B3","VÍA:","",0)
        insertTextIntoCell(oTable,"C3",via, viacargo,0)

        insertTextIntoCell(oTable,"B4","DE:","",0)
        insertTextIntoCell(oTable,"C4",de,decargo,0)

        insertTextIntoCell(oTable,"A5","","",0)
        insertTextIntoCell(oTable,"B5","REF.:","",0)
        insertTextIntoCell(oTable,"C5",referencia,"",0)

        insertTextIntoCell(oTable,"A6","","",1)
        insertTextIntoCell(oTable,"B6","FECHA:","",1)
        insertTextIntoCell(oTable,"C6",fecha,"",1)
        insertTextIntoCell(oTable,"G6",hojaruta,"",1)

        mergeCell(oCursor,"C2", 5)
        mergeCell(oCursor,"C3", 5)
        mergeCell(oCursor,"C4", 5)
        mergeCell(oCursor,"C5", 5)
        mergeCell(oCursor,"G6", 1)
        mergeCell(oCursor,"C6", 3)

        FormatCell(oTable,"C2",0)
        FormatCell(oTable,"C3",0)
        FormatCell(oTable,"C4",0)
        FormatCell(oTable,"C5",0)
        FormatCell(oTable,"C6",1)
        FormatCell(oTable,"D6",1)

      oTable.getRows().removeByIndex(0, 1);

      oSize = uno.createUnoStruct( "com.sun.star.awt.Size" )
      oSize.Width = 3000
      oSize.Height = 1300


      from com.sun.star.text.WrapTextMode import THROUGHT
      img = oDoc.createInstance('com.sun.star.text.TextGraphicObject')
      img.GraphicURL = "https://correspondenciapruebas.endesyc.bo/laravel/img/logo_endesyc.png"
      img.setSize(oSize)
      img.HoriOrient = 7
      img.LeftMargin = 0
      img.TopMargin = 0
      img.Surround = THROUGHT

      oStyle = oDoc.StyleFamilies.getByName("PageStyles").getByName("Standard")
      oStyle.setPropertyValue("HeaderIsOn",True)
      #oStyle.setPropertyValue("HeaderTopBorderDistance",0)
      oHtext = oStyle.HeaderText
      oHtext.insertTextContent(oHtext.Start, img, False)
    else:
      cursor = oText.createTextCursor()
      cursor.setPropertyValue("CharHeight",12.0)
      cursor.setPropertyValue("CharWeight", FW_NORMAL)
      cursor.setPropertyValue("CharFontName", "Arial")
      oText.insertString(cursor, "La Paz, "+fecha, 0)
      oText.insertControlCharacter( cursor, 0, False )
      cursor.setPropertyValue("CharWeight", FW_BOLD)
      oText.insertString(cursor, "ENDE SYC - "+cite, False)
      oText.insertControlCharacter( cursor, 0, False )
      oText.insertControlCharacter( cursor, 0, False )

      parrafo = 0
      while oEnum.hasMoreElements():
           oPar = oEnum.nextElement()
           if oPar.supportsService("com.sun.star.text.Paragraph"):
              parrafo = parrafo+1
              if parrafo == 1:
                oPar.ParaAdjust= RIGHT
              elif parrafo == 2:
                oPar.ParaAdjust= RIGHT
              else:
                  break
