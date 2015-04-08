/*
 * File: app/view/administracion/local/empleados/cargos.js
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

Ext.define('siadno.view.administracion.local.empleados.cargos', {
    extend: 'Ext.window.Window',
    alias: 'widget.AdministracionLocalEmpleadosCargos',

    height: 304,
    width: 364,
    resizable: false,
    layout: {
        type: 'fit'
    },
    closable: false,
    iconCls: 'icon-folder_user',
    title: 'Cargos V.I.P',

    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            items: [
                {
                    xtype: 'gridpanel',
                    store: 'dumyStore',
                    columns: [
                        {
                            xtype: 'gridcolumn',
                            renderer: function(value, metaData, record, rowIndex, colIndex, store, view) {
                                if(Ext.isEmpty(value)){
                                    return "<font style='color:gray'>--Nuevo Cargo--</font>";    
                                }

                                return value;
                            },
                            width: 228,
                            dataIndex: 'nombre',
                            text: 'Cargo',
                            editor: {
                                xtype: 'textareafield'
                            }
                        },
                        {
                            xtype: 'booleancolumn',
                            dataIndex: 'estado',
                            text: 'Activo?',
                            falseText: 'In Activo',
                            trueText: 'Activo',
                            undefinedText: 'No Definido',
                            editor: {
                                xtype: 'checkboxfield',
                                name: 'estado',
                                boxLabel: 'Activo?'
                            }
                        }
                    ],
                    selModel: Ext.create('Ext.selection.RowModel', {

                    }),
                    plugins: [
                        Ext.create('Ext.grid.plugin.RowEditing', {

                        })
                    ],
                    dockedItems: [
                        {
                            xtype: 'toolbar',
                            dock: 'bottom',
                            items: [
                                {
                                    xtype: 'buttongroup',
                                    title: 'Acciones',
                                    columns: 5,
                                    layout: {
                                        columns: 2,
                                        type: 'table'
                                    },
                                    items: [
                                        {
                                            xtype: 'button',
                                            accion: 'nuevo',
                                            iconCls: 'icon-page_add',
                                            text: 'Nuevo',
                                            listeners: {
                                                click: {
                                                    fn: me.onButtonClickNuevo,
                                                    scope: me
                                                }
                                            }
                                        },
                                        {
                                            xtype: 'button',
                                            accion: 'guardar',
                                            iconCls: 'icon-page_save',
                                            text: 'Guardar',
                                            listeners: {
                                                click: {
                                                    fn: me.onButtonClickGuardar,
                                                    scope: me
                                                }
                                            }
                                        },
                                        {
                                            xtype: 'button',
                                            iconCls: 'icon-page_cancel',
                                            text: 'Cancelar',
                                            listeners: {
                                                click: {
                                                    fn: me.onButtonClickCancelar,
                                                    scope: me
                                                }
                                            }
                                        },
                                        {
                                            xtype: 'button',
                                            aacion: 'borrar',
                                            iconCls: 'icon-page_delete',
                                            text: 'Borrar',
                                            listeners: {
                                                click: {
                                                    fn: me.onButtonClickBorrar,
                                                    scope: me
                                                }
                                            }
                                        }
                                    ]
                                },
                                {
                                    xtype: 'tbfill'
                                },
                                {
                                    xtype: 'buttongroup',
                                    columns: 1,
                                    layout: {
                                        columns: 1,
                                        type: 'table'
                                    },
                                    items: [
                                        {
                                            xtype: 'button',
                                            iconCls: 'icon-door_out',
                                            text: 'Salir',
                                            listeners: {
                                                click: {
                                                    fn: me.onButtonClickSalir,
                                                    scope: me
                                                }
                                            }
                                        }
                                    ]
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

    onButtonClickNuevo: function(button, e, eOpts) {
        var grid=Ext.ComponentQuery.query('AdministracionLocalEmpleadosCargos grid')[0];
        siadno.nuevoRegistroGrid(grid,'idcargo');
    },

    onButtonClickGuardar: function(button, e, eOpts) {
        var grid=Ext.ComponentQuery.query('AdministracionLocalEmpleadosCargos grid')[0];         
        siadno.enviarCambiosGrid(grid, null, 'idcargo');            
    },

    onButtonClickCancelar: function(button, e, eOpts) {
        var grid=Ext.ComponentQuery.query('AdministracionLocalEmpleadosCargos grid')[0];
        siadno.rollbackCambiosGrid(grid);         
    },

    onButtonClickBorrar: function(button, e, eOpts) {
        var grid=Ext.ComponentQuery.query('AdministracionLocalEmpleadosCargos grid')[0];
        siadno.borrarRegistroGrid(grid, null, 'idcargo');    
    },

    onButtonClickSalir: function(button, e, eOpts) {
        this.close();
    },

    onWindowBeforeShow: function(component, eOpts) {
        var clase=Ext.ClassManager.get("siadno.store.administracion.personas.cargos");
        if(Ext.isEmpty(clase)){
            Ext.define('siadno.store.administracion.personas.cargos', {
                extend: 'Ext.data.Store',

                constructor: function(cfg) {
                    var me = this;
                    cfg = cfg || {};
                    me.callParent([Ext.apply({
                        storeId: Ext.id(),
                        idLocal: -1,
                        proxy: {
                            type: 'ajax',
                            url: 'clases/interfaces/mantenimiento/local/personas/InterfazCargos.php',
                            reader: {
                                type: 'json',
                                idProperty: 'idcargo',
                                messageProperty: 'msg',
                                root: 'data'
                            }
                        },
                        fields: [
                        {
                            name: 'idcargo'
                        },
                        {
                            name: 'nombre'
                        },
                        {
                            name: 'estado',
                            type: 'boolean'
                        }
                        ]
                    }, cfg)]);
                }
            });
        }


        var grid=Ext.ComponentQuery.query('AdministracionLocalEmpleadosCargos grid')[0];
        grid.reconfigure(Ext.create('siadno.store.administracion.personas.cargos'));
        grid.getStore().load();
    }

});