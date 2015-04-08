/*
 * File: app/view/administracion/local/auditoria/informe.js
 *
 * This file was generated by Sencha Architect version 2.2.2.
 * http://www.sencha.com/products/architect/
 *
 * This file requires use of the Ext JS 4.0.x library, under independent license.
 * License of Sencha Architect does not include license for Ext JS 4.0.x. For more
 * details see http://www.sencha.com/license or contact license@sencha.com.
 *
 * This file will be auto-generated each and everytime you save your project.
 *
 * Do NOT hand edit this file.
 */

Ext.define('siadno.view.administracion.local.auditoria.informe', {
    extend: 'Ext.window.Window',
    alias: 'widget.AdministracionLocalAuditoriaInforme',

    height: 600,
    width: 800,
    layout: {
        align: 'stretch',
        type: 'vbox'
    },
    iconCls: 'icon-bug_magnify',
    title: 'Informe Básico de Auditoria',

    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            items: [
                {
                    xtype: 'form',
                    flex: 1,
                    height: 120,
                    maxHeight: 120,
                    minHeight: 120,
                    padding: 0,
                    layout: {
                        align: 'stretch',
                        type: 'hbox'
                    },
                    bodyPadding: 10,
                    items: [
                        {
                            xtype: 'fieldset',
                            flex: 1,
                            maxWidth: 200,
                            title: 'Fecha de Actividad',
                            items: [
                                {
                                    xtype: 'datefield',
                                    anchor: '100%',
                                    fieldLabel: 'Desde',
                                    labelWidth: 50,
                                    name: 'desde',
                                    format: 'd/m/Y',
                                    submitFormat: 'Y-m-d 00:00:00',
                                    listeners: {
                                        change: {
                                            fn: me.onDatefieldChange,
                                            scope: me
                                        }
                                    }
                                },
                                {
                                    xtype: 'datefield',
                                    anchor: '100%',
                                    fieldLabel: 'Hasta',
                                    labelWidth: 50,
                                    name: 'hasta',
                                    format: 'd/m/Y',
                                    submitFormat: 'Y-m-d 23:59:59',
                                    listeners: {
                                        change: {
                                            fn: me.onDatefieldChange1,
                                            scope: me
                                        }
                                    }
                                }
                            ]
                        },
                        {
                            xtype: 'fieldset',
                            flex: 1,
                            title: 'Módulo o Clase',
                            items: [
                                {
                                    xtype: 'combobox',
                                    anchor: '100%',
                                    fieldLabel: 'Módulo',
                                    labelWidth: 50,
                                    name: 'idmodulo',
                                    value: 0,
                                    displayField: 'abreviatura',
                                    store: 'dumyStore',
                                    valueField: 'idmodulo',
                                    listeners: {
                                        change: {
                                            fn: me.onComboboxChange,
                                            scope: me
                                        }
                                    }
                                },
                                {
                                    xtype: 'combobox',
                                    anchor: '100%',
                                    fieldLabel: 'Clase',
                                    labelWidth: 50,
                                    name: 'idclase',
                                    value: 0,
                                    displayField: 'nombre',
                                    store: 'dumyStore',
                                    valueField: 'idclase',
                                    listeners: {
                                        change: {
                                            fn: me.onComboboxChange1,
                                            scope: me
                                        }
                                    }
                                }
                            ]
                        },
                        {
                            xtype: 'fieldset',
                            flex: 1,
                            title: 'Otros',
                            items: [
                                {
                                    xtype: 'combobox',
                                    anchor: '100%',
                                    fieldLabel: 'Usuario',
                                    labelWidth: 50,
                                    name: 'idusuario',
                                    value: 0,
                                    displayField: 'nombres',
                                    store: 'dumyStore',
                                    valueField: 'idusuario',
                                    listeners: {
                                        change: {
                                            fn: me.onComboboxChange2,
                                            scope: me
                                        }
                                    }
                                },
                                {
                                    xtype: 'textfield',
                                    anchor: '100%',
                                    fieldLabel: 'Texto',
                                    labelWidth: 50,
                                    name: 'descripcion',
                                    listeners: {
                                        change: {
                                            fn: me.onTextfieldChange,
                                            scope: me
                                        }
                                    }
                                }
                            ]
                        }
                    ]
                },
                {
                    xtype: 'gridpanel',
                    flex: 1,
                    store: 'dumyStore',
                    columns: [
                        {
                            xtype: 'gridcolumn',
                            width: 116,
                            dataIndex: 'fechaauditoria',
                            text: 'Fecha y Hora'
                        },
                        {
                            xtype: 'gridcolumn',
                            renderer: function(value, metaData, record, rowIndex, colIndex, store, view) {
                                if(Ext.isEmpty(value) || value==0){
                                    return '';
                                }
                                //alert(2);
                                //alert(this.moduloStore);
                                var indice=this.moduloStore.findExact('idmodulo', value);
                                if(indice>=0){
                                    var registro=this.moduloStore.getAt(indice);
                                    return registro.get('abreviatura');
                                }

                                return '';
                            },
                            width: 199,
                            dataIndex: 'idmodulo',
                            text: 'Modulo'
                        },
                        {
                            xtype: 'gridcolumn',
                            renderer: function(value, metaData, record, rowIndex, colIndex, store, view) {
                                if(Ext.isEmpty(value) || value==0){
                                    return '';
                                }

                                var indice=this.claseStore.findExact('idclase', value);
                                if(indice>=0){
                                    var registro=this.claseStore.getAt(indice);
                                    return registro.get('nombre');
                                }

                                return '';
                            },
                            width: 93,
                            dataIndex: 'idclase',
                            text: 'Clase'
                        },
                        {
                            xtype: 'gridcolumn',
                            renderer: function(value, metaData, record, rowIndex, colIndex, store, view) {
                                var valor=value;
                                if(Ext.isEmpty(valor) || valor==0){    
                                    valor=record.get('idaccionauditableauditoria');        
                                }

                                var indice=this.accionStore.findExact('idaccionauditable', valor);
                                var registro=this.accionStore.getAt(indice);
                                return registro.get('nombre');
                            },
                            width: 86,
                            dataIndex: 'idaccionauditable',
                            text: 'Acción'
                        },
                        {
                            xtype: 'gridcolumn',
                            width: 190,
                            dataIndex: 'descripcion',
                            text: 'Descripción'
                        },
                        {
                            xtype: 'gridcolumn',
                            renderer: function(value, metaData, record, rowIndex, colIndex, store, view) {
                                if(Ext.isEmpty(value) || value==0){
                                    return '';
                                }

                                var indice=this.usuarioStore.findExact('idusuario', value);
                                if(indice>=0){
                                    var registro=this.usuarioStore.getAt(indice);
                                    return registro.get('nombres');
                                }

                                return '';
                            },
                            dataIndex: 'idusuario',
                            text: 'Usuario'
                        }
                    ],
                    dockedItems: [
                        {
                            xtype: 'pagingtoolbar',
                            dock: 'bottom',
                            width: 360,
                            displayInfo: true,
                            store: 'dumyStore',
                            items: [
                                {
                                    xtype: 'tbseparator'
                                },
                                {
                                    xtype: 'button',
                                    id: 'imprimir',
                                    iconCls: 'icon-printer_start',
                                    listeners: {
                                        click: {
                                            fn: me.onImprimirClick,
                                            scope: me
                                        }
                                    }
                                },
                                {
                                    xtype: 'button',
                                    id: 'pdf',
                                    iconCls: 'icon-pdf',
                                    listeners: {
                                        click: {
                                            fn: me.onPdfClick,
                                            scope: me
                                        }
                                    }
                                },
                                {
                                    xtype: 'button',
                                    id: 'excel',
                                    iconCls: 'icon-excel',
                                    listeners: {
                                        click: {
                                            fn: me.onExcelClick,
                                            scope: me
                                        }
                                    }
                                },
                                {
                                    xtype: 'tbseparator'
                                }
                            ]
                        }
                    ]
                }
            ],
            listeners: {
                beforeshow: {
                    fn: me.onWindowBeforeShow,
                    scope: me
                }
            }
        });

        me.callParent(arguments);
    },

    onDatefieldChange: function(field, newValue, oldValue, eOpts) {
        this.setParametrosDeBusqueda('desde', field.getSubmitValue());
    },

    onDatefieldChange1: function(field, newValue, oldValue, eOpts) {
        this.setParametrosDeBusqueda('hasta', field.getSubmitValue());
    },

    onComboboxChange: function(field, newValue, oldValue, eOpts) {
        this.setParametrosDeBusqueda('idmodulo', newValue);
    },

    onComboboxChange1: function(field, newValue, oldValue, eOpts) {
        this.setParametrosDeBusqueda('idclase', newValue);
    },

    onComboboxChange2: function(field, newValue, oldValue, eOpts) {
        this.setParametrosDeBusqueda('idusuario', newValue);
    },

    onTextfieldChange: function(field, newValue, oldValue, eOpts) {
        this.setParametrosDeBusqueda('descripcion', newValue);
    },

    onImprimirClick: function(button, e, eOpts) {
        var grid=Ext.ComponentQuery.query('AdministracionLocalAuditoriaInforme pagingtoolbar')[0];

        var callback=function(records, operation, succes){
            try{                    
                var result = Ext.decode(operation.response.responseText);        
                siadno.imprimirPostConsulta(result);
            }catch(e){            
                if(!Ext.isEmpty(e.message)){
                    Ext.MessageBox.alert('Error codificar la respuesta', e.message);
                }else{
                    Ext.MessageBox.alert('Error codificar la respuesta', e);
                }        
            }
        }

        grid.getStore().getProxy().extraParams.imprimir=1;
        grid.getStore().load({scope:this, callback:callback});
        grid.getStore().getProxy().extraParams.imprimir=0;
    },

    onPdfClick: function(button, e, eOpts) {
        var grid=Ext.ComponentQuery.query('AdministracionLocalAuditoriaInforme pagingtoolbar')[0];

        var callback=function(records, operation, succes){
            try{                    
                var result = Ext.decode(operation.response.responseText);        
                siadno.descargarPostConsulta(result);
            }catch(e){            
                if(!Ext.isEmpty(e.message)){
                    Ext.MessageBox.alert('Error codificar la respuesta', e.message);
                }else{
                    Ext.MessageBox.alert('Error codificar la respuesta', e);
                }        
            }
        }

        grid.getStore().getProxy().extraParams.pdf=1;
        grid.getStore().load({scope:this, callback:callback});
        grid.getStore().getProxy().extraParams.pdf=0;
    },

    onExcelClick: function(button, e, eOpts) {
        var grid=Ext.ComponentQuery.query('AdministracionLocalAuditoriaInforme pagingtoolbar')[0];

        var callback=function(records, operation, succes){
            try{                    
                var result = Ext.decode(operation.response.responseText);        
                siadno.descargarPostConsulta(result);
            }catch(e){            
                if(!Ext.isEmpty(e.message)){
                    Ext.MessageBox.alert('Error codificar la respuesta', e.message);
                }else{
                    Ext.MessageBox.alert('Error codificar la respuesta', e);
                }        
            }
        }

        grid.getStore().getProxy().extraParams.excel=1;
        grid.getStore().load({scope:this, callback:callback});
        grid.getStore().getProxy().extraParams.excel=0;
    },

    onWindowBeforeShow: function(component, eOpts) {
        //paginador.moveFirst();
        //this.application.activarToolbar('AdministracionLocalAuditoriaInforme', true);

        var clase=Ext.ClassManager.get("siadno.store.sistema.usuarios");
        if(Ext.isEmpty(clase)){
            Ext.define('siadno.store.sistema.usuarios', {
                extend: 'Ext.data.Store',

                constructor: function(cfg) {
                    var me = this;
                    cfg = cfg || {};
                    me.callParent([Ext.apply({
                        storeId: Ext.id(),
                        proxy: {
                            type: 'ajax',
                            url: 'clases/data/sistema/usuarios.php',
                            reader: {
                                type: 'json',
                                idProperty: 'idusuario',
                                messageProperty: 'msg',
                                root: 'data'
                            }
                        },
                        fields: [
                        {
                            name: 'idusuario'
                        },
                        {
                            name: 'nombres'
                        }
                        ]
                    }, cfg)]);
                }
            });
        }

        clase=Ext.ClassManager.get("siadno.store.sistema.clases");
        if(Ext.isEmpty(clase)){
            Ext.define('siadno.store.sistema.clases', {
                extend: 'Ext.data.Store',

                constructor: function(cfg) {
                    var me = this;
                    cfg = cfg || {};
                    me.callParent([Ext.apply({
                        storeId: Ext.id(),
                        proxy: {
                            type: 'ajax',
                            url: 'clases/data/sistema/clases.php',
                            reader: {
                                type: 'json',
                                idProperty: 'idclase',
                                messageProperty: 'msg',
                                root: 'data'
                            }
                        },
                        fields: [
                        {
                            name: 'idclase'
                        },
                        {
                            name: 'nombre'
                        }
                        ]
                    }, cfg)]);
                }
            });
        }

        clase=Ext.ClassManager.get("siadno.store.sistema.modulos");
        if(Ext.isEmpty(clase)){
            Ext.define('siadno.store.sistema.modulos', {
                extend: 'Ext.data.Store',

                constructor: function(cfg) {
                    var me = this;
                    cfg = cfg || {};
                    me.callParent([Ext.apply({
                        storeId: Ext.id(),
                        proxy: {
                            type: 'ajax',
                            url: 'clases/data/sistema/modulos.php',
                            reader: {
                                type: 'json',
                                idProperty: 'idmodulo',
                                messageProperty: 'msg',
                                root: 'data'
                            }
                        },
                        fields: [
                        {
                            name: 'idmodulo'
                        },
                        {
                            name: 'clase'
                        },
                        {
                            name: 'nombre'
                        },
                        {
                            name: 'abreviatura'
                        }
                        ]
                    }, cfg)]);
                }
            });
        }

        clase=Ext.ClassManager.get("siadno.store.sistema.accionesauditables");
        if(Ext.isEmpty(clase)){
            Ext.define('siadno.store.sistema.accionesauditables', {
                extend: 'Ext.data.Store',

                constructor: function(cfg) {
                    var me = this;
                    cfg = cfg || {};
                    me.callParent([Ext.apply({
                        storeId: Ext.id(),
                        proxy: {
                            type: 'ajax',
                            url: 'clases/data/sistema/accionesAuditables.php',
                            reader: {
                                type: 'json',
                                idProperty: 'idaccionauditable',
                                messageProperty: 'msg',
                                root: 'data'
                            }
                        },
                        fields: [
                        {
                            name: 'idaccionauditable'
                        },                
                        {
                            name: 'nombre'
                        }
                        ]
                    }, cfg)]);
                }
            });
        }

        clase=Ext.ClassManager.get("siadno.store.administracion.local.auditoria.informeBasico");
        if(Ext.isEmpty(clase)){
            Ext.define('siadno.store.administracion.local.auditoria.informeBasico', {
                extend: 'Ext.data.Store',

                constructor: function(cfg) {
                    var me = this;
                    cfg = cfg || {};
                    me.callParent([Ext.apply({
                        storeId: Ext.id(),
                        pageSize: 18,
                        proxy: {
                            type: 'ajax',
                            url: 'clases/interfaces/mantenimiento/local/auditoria/InterfazInformeBasico.php',
                            reader: {
                                type: 'json',
                                idProperty: 'id',
                                messageProperty: 'msg',
                                root: 'data'
                            }
                        },
                        fields: [
                        {
                            name: 'id'
                        },
                        {
                            name: 'descripcion'
                        },
                        {
                            name: 'modificacion'
                        },
                        {
                            name: 'fechaauditoria'
                        },
                        {
                            name: 'idauditoria'
                        },
                        {
                            name: 'idusuario'
                        },
                        {
                            name: 'idaccionauditableauditoria'
                        },
                        {
                            name: 'idaccionauditable'
                        },
                        {
                            name: 'idmodificacion'
                        },
                        {
                            name: 'idclase'
                        },
                        {
                            name: 'idmodulo'
                        }
                        ]
                    }, cfg)]);
                }
            });
        }


        var grid=Ext.ComponentQuery.query('AdministracionLocalAuditoriaInforme grid')[0];
        grid.informeStore=Ext.create('siadno.store.administracion.local.auditoria.informeBasico');
        grid.reconfigure(grid.informeStore);

        var paginador=Ext.ComponentQuery.query('AdministracionLocalAuditoriaInforme pagingtoolbar')[0];
        paginador.bindStore(grid.informeStore);

        grid.accionStore=Ext.create('siadno.store.sistema.accionesauditables');
        grid.accionStore.load();

        var combo=Ext.ComponentQuery.query('AdministracionLocalAuditoriaInforme combobox[name=idusuario]')[0];
        grid.usuarioStore=Ext.create('siadno.store.sistema.usuarios');
        combo.bindStore(grid.usuarioStore);
        combo.getStore().load();

        combo=Ext.ComponentQuery.query('AdministracionLocalAuditoriaInforme combobox[name=idclase]')[0];
        grid.claseStore=Ext.create('siadno.store.sistema.clases');
        combo.bindStore(grid.claseStore);
        combo.getStore().load();

        combo=Ext.ComponentQuery.query('AdministracionLocalAuditoriaInforme combobox[name=idmodulo]')[0];
        grid.moduloStore=Ext.create('siadno.store.sistema.modulos');
        combo.bindStore(grid.moduloStore);
        combo.getStore().load();

        var date=Ext.ComponentQuery.query('AdministracionLocalAuditoriaInforme datefield[name=desde]')[0];
        date.setValue(new Date());
        date=Ext.ComponentQuery.query('AdministracionLocalAuditoriaInforme datefield[name=hasta]')[0];
        date.setValue(new Date());
    },

    setParametrosDeBusqueda: function(campo, valor) {
        var grid=Ext.ComponentQuery.query('AdministracionLocalAuditoriaInforme grid')[0];
        grid.getStore().getProxy().extraParams[campo]=valor;
        //grid.getStore().getProxy().extraParams.start=0;
        //grid.getStore().getProxy().extraParams.page=0;
        //grid.getStore().getProxy().extraParams.imprimir=0;
        //alert(grid.getStore().getProxy().baseParams);
        //grid.getStore().load();
        //alert('ok');
        //var paginador=Ext.ComponentQuery.query('AdministracionLocalAuditoriaInforme pagingtoolbar')[0];
        //pagingToolbar.loading.hide();
        //paginador.moveFirst();
        //this.application.activarToolbar('AdministracionLocalAuditoriaInforme', true);
    }

});