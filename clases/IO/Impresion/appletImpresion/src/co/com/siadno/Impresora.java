package co.com.siadno;

import java.applet.Applet;
import java.awt.HeadlessException;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.net.URI;
import java.net.URL;
import java.security.AccessController;
import java.security.PrivilegedAction;
import javax.print.*;
import javax.print.attribute.HashPrintRequestAttributeSet;
import javax.print.attribute.PrintRequestAttributeSet;
import javax.print.attribute.standard.Destination;
import javax.swing.JOptionPane;

/**
 *
 * @author Mauricio
 */
public class Impresora extends Applet {
    //private static DocFlavor formatoImpresion = DocFlavor.INPUT_STREAM.AUTOSENSE ;
    private static PrintRequestAttributeSet atributos = new HashPrintRequestAttributeSet();

    /**
     * Obtiene el listado de impresoras instaladas en el sistema
     * @return String JSON con las impresoras instaladas en el sistema
     */
    public static String listarImpresoras(){
        PrintService[] impresoras;
        StringBuilder impresorasJSON= new StringBuilder("");

        try{
            impresoras=(PrintService[])AccessController.doPrivileged(new PrivilegedAction<Object>() {
                @Override
                public PrintService[] run() {
                    PrintService[] impresoras = PrintServiceLookup.lookupPrintServices(DocFlavor.INPUT_STREAM.AUTOSENSE, atributos);
                    return impresoras;
                }
            });

            for(int i=0; i<impresoras.length; i++){
                impresorasJSON.append(impresoras[i].getName());
                if(i<impresoras.length-1)
                    impresorasJSON.append("|");
            }
            return impresorasJSON.toString();
        }catch(Exception e){
            System.out.println("Error al lista impresoras: "+e.getMessage());
            JOptionPane.showMessageDialog(null, "Se ha generado un error al listar las impresoras", "Error", JOptionPane.ERROR_MESSAGE);
            return "";
        }
    }

    /**
     * Imprime un archivo de secuencias de escape IBM/P 1.0 o PCL 5.0 en una
     * impresora instalada en el sistema
     * @param url Url del archivo que contiene la secuencias de escape
     * @param numeroImpresora 1 si se debe imprimir en la impresora principal
     * @param enviarBarCode true si se imprimira barcode en PCL
     * @return Devuelve true si la impresion pudo ser enviada
     */
    public static void imprimir(final String nombreImpresora, final String idImpresion, final boolean mostrarMensaje, final String rutaParaGuardar){
        AccessController.doPrivileged(new PrivilegedAction<Object>() {
            @Override
            public Object run() {
                try{
                    PrintService impresora=null;
                    System.out.println("nombre impresora: "+nombreImpresora);
                    PrintService[] impresoras = PrintServiceLookup.lookupPrintServices(DocFlavor.INPUT_STREAM.AUTOSENSE, atributos);
                    for(int i=0; i<impresoras.length; i++){
                        if(impresoras[i].getName().equals(nombreImpresora)){
                            impresora=impresoras[i];
                            break;
                        }
                    }

                    if(impresora==null){
                        JOptionPane.showMessageDialog(null, "No se encontro la impresora "+nombreImpresora , "Error", JOptionPane.ERROR_MESSAGE);
                        return null;
                    }

                    DocPrintJob impresion = impresora.createPrintJob();
                    URL documentoURL= new URL("http://www.siadno.com/clases/interfaces/sistema/impresion/InterfazAdministracionImpresoras.php?accion=1001&idImpresion="+idImpresion);
                    SimpleDoc documento = new SimpleDoc(documentoURL.openStream(),DocFlavor.INPUT_STREAM.AUTOSENSE, null);

                    if(!rutaParaGuardar.isEmpty()){
                        byte[] b = new byte[1024];
                        int leidos=0;
                        InputStream in=documento.getStreamForBytes();
                        FileOutputStream f= new FileOutputStream(rutaParaGuardar);
                        do{
                            leidos=in.read(b);
                            if(leidos<0)
                                break;

                            f.write(b, 0, leidos);
                        }while(leidos>=0);
                        f.close();
                    }

                    impresion.print(documento, atributos);
                    if(mostrarMensaje){
                        JOptionPane.showMessageDialog(null, "La impresión ha sido enviada" , "Informacion", JOptionPane.INFORMATION_MESSAGE);
                    }
                }catch(HeadlessException | IOException | PrintException e){
                    System.out.println(e.getMessage());
                    e.printStackTrace(System.out);                    
                    JOptionPane.showMessageDialog(null, "Error al imprimir: "+e.getMessage() , "Error", JOptionPane.ERROR_MESSAGE);
                }
                return null;
            }
        });
    }
}
