const { app, BrowserWindow, Menu } = require('electron');
const path = require('path');

const pkg = require('./package.json');

let mainWindow;

// Configuration locale WAMP (PHP / interface web à jour dans www\attendance_system)
const WAMP_URL = 'http://localhost/attendance_system/';

function createWindow() {
  mainWindow = new BrowserWindow({
    width: 1200,
    height: 800,
    title: `Gestion de Présence v${pkg.version}`,
    webPreferences: {
      preload: path.join(__dirname, 'preload.js'),
      nodeIntegration: false,
      contextIsolation: true
    },
    icon: path.join(__dirname, 'assets/icon.png')
  });

  mainWindow.loadURL(WAMP_URL);
  mainWindow.webContents.on('did-finish-load', () => {
    mainWindow.setTitle(`Gestion de Présence v${pkg.version}`);
  });

  // Dev tools (optionnel)
  // mainWindow.webContents.openDevTools();

  mainWindow.on('closed', () => {
    mainWindow = null;
  });
}

app.on('ready', createWindow);

app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') {
    app.quit();
  }
});

app.on('activate', () => {
  if (mainWindow === null) {
    createWindow();
  }
});

// Menu
const template = [
  {
    label: 'Fichier',
    submenu: [
      {
        label: 'Quitter',
        accelerator: 'CmdOrCtrl+Q',
        click: () => app.quit()
      }
    ]
  },
  {
    label: 'Affichage',
    submenu: [
      {
        label: 'Actualiser',
        accelerator: 'CmdOrCtrl+R',
        click: () => mainWindow.reload()
      },
      {
        label: 'Outils de développement',
        accelerator: 'CmdOrCtrl+I',
        click: () => mainWindow.webContents.toggleDevTools()
      }
    ]
  }
];

Menu.setApplicationMenu(Menu.buildFromTemplate(template));


