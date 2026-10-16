// 1. MUST BE AT THE VERY TOP: Load environment variables properly with ES Modules
import dotenv from 'dotenv';
import path from 'path';
import { fileURLToPath } from 'url';

// Recreate __dirname for ES Module scope
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Explicitly point to the .env file in the root directory
dotenv.config({ path: path.join(__dirname, '.env') });

// Debug check to verify your environment variables are loading
console.log('Loaded Environment Variables check:');
console.log(`- DB_HOST: ${process.env.DB_HOST || '(using fallback localhost)'}`);
console.log(`- DB_NAME: ${process.env.DB_NAME || '(using fallback db_changeme)'}`);

import express from 'express';
import { spawn } from 'child_process';
import fs from 'fs';
import cors from 'cors';
import cron from 'node-cron';
import mysql from 'mysql2/promise';

const app = express();
app.use(cors());
app.use(express.json());

const BACKUP_DIR = path.join(__dirname, 'backups');

// Ensure the backup directory exists
if (!fs.existsSync(BACKUP_DIR)) {
  fs.mkdirSync(BACKUP_DIR);
}

// Database configuration for XAMPP (reads from loaded .env variables with fallbacks)
const dbConfig = {
  host: process.env.DB_HOST || 'localhost',
  user: process.env.DB_USER || 'root',
  password: process.env.DB_PASSWORD || '', // Leave empty if XAMPP root has no password
  database: process.env.DB_NAME || 'db_changeme'
};

// Global variables to store current automation settings & active cron job
let scheduledTask = null;
let currentAutomationConfig = {
  autoBackup: true,
  frequency: 'Every 12 Hours', // Set to every 12 hours by default
  exportScope: 'full',
  tables: []
};

// Function to delete backups older than 2 weeks (14 days)
function cleanOldBackups() {
  fs.readdir(BACKUP_DIR, (err, files) => {
    if (err) {
      console.error('Failed to read backup directory for cleanup:', err);
      return;
    }

    const twoWeeksAgo = Date.now() - (14 * 24 * 60 * 60 * 1000);

    files.forEach(file => {
      // Only target sql backup files
      if (file.endsWith('.sql')) {
        const filePath = path.join(BACKUP_DIR, file);
        
        fs.stat(filePath, (err, stats) => {
          if (err) {
            console.error(`Failed to get stats for file ${file}:`, err);
            return;
          }

          // Check if file modification time is older than 2 weeks
          if (stats.mtimeMs < twoWeeksAgo) {
            fs.unlink(filePath, (err) => {
              if (err) {
                console.error(`Failed to delete old backup ${file}:`, err);
              } else {
                console.log(`Successfully deleted old backup: ${file}`);
              }
            });
          }
        });
      }
    });
  });
}

// Function to perform the MySQL dump using XAMPP's mysqldump formatted like phpMyAdmin
function executeMySQLBackup(options = {}, callback) {
  const { exportScope = 'full', tables = [] } = options;
  
  const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
  const fileName = `mysql_backup_${timestamp}.sql`;
  const filePath = path.join(BACKUP_DIR, fileName);
  
  const dbUser = process.env.DB_USER || 'root';
  const dbPass = process.env.DB_PASSWORD || ''; 
  const dbName = process.env.DB_NAME || 'db_changeme';
  const mysqldumpPath = process.env.MYSQLDUMP_PATH || "C:\\xampp\\mysql\\bin\\mysqldump.exe";

  // Build argument array mimicking phpMyAdmin exports
  const args = [
    `-u${dbUser}`
  ];

  if (dbPass) {
    args.push(`-p${dbPass}`);
  }

  // Standard phpMyAdmin-like options for compatibility and clean structure
  args.push(
    '--add-drop-table',
    '--add-locks',
    '--create-options',
    '--disable-keys',
    '--extended-insert',
    '--quick',
    '--set-charset'
  );

  // Handle Export Scope flags
  if (exportScope === 'structure') {
    args.push('--no-data'); // Equivalent to phpMyAdmin "Structure only"
  } else if (exportScope === 'data') {
    args.push('--no-create-info'); // Equivalent to phpMyAdmin "Data only"
  }

  // Add database name
  args.push(dbName);

  // Add specific tables if provided
  if (Array.isArray(tables) && tables.length > 0) {
    args.push(...tables);
  }

  const writeStream = fs.createWriteStream(filePath);
  const mysqldump = spawn(mysqldumpPath, args);

  mysqldump.stdout.pipe(writeStream);

  let stderrData = '';
  mysqldump.stderr.on('data', (data) => {
    stderrData += data.toString();
  });

  mysqldump.on('error', (error) => {
    console.error(`Process error: ${error.message}`);
    if (callback) callback(false, error.message);
  });

  mysqldump.on('close', (code) => {
    if (code === 0) {
      console.log(`Backup created successfully: ${fileName}`);
      
      // Automatically purge backups older than 2 weeks after a successful dump
      cleanOldBackups();

      if (callback) callback(true, fileName);
    } else {
      console.error(`Backup failed with code ${code}: ${stderrData}`);
      if (fs.existsSync(filePath)) fs.unlinkSync(filePath);
      if (callback) callback(false, stderrData || `Exit code ${code}`);
    }
  });
}

// Helper function to map frequency labels to Cron expressions
function getCronExpression(frequency) {
  switch (frequency) {
    case 'Hourly':
      return '0 * * * *';          // Every hour at minute 0
    case 'Every 12 Hours':
      return '0 */12 * * *';       // Every 12 hours (at 00:00 and 12:00)
    case 'Daily at 00:00':
      return '0 0 * * *';          // Every day at midnight
    case 'Weekly (Sunday at 02:00)':
      return '0 2 * * 0';          // Every Sunday at 2:00 AM
    default:
      return '0 0 * * *';          // Default to daily midnight
  }
}

// Function to initialize or update the cron scheduler dynamically
function updateCronJob(config) {
  // Stop existing job if it exists
  if (scheduledTask) {
    scheduledTask.stop();
    scheduledTask = null;
  }

  if (!config.autoBackup) {
    console.log('Automated backups are currently DISABLED.');
    return;
  }

  const cronExpression = getCronExpression(config.frequency);
  console.log(`Scheduling automated backups with frequency: "${config.frequency}" (${cronExpression})`);

  scheduledTask = cron.schedule(cronExpression, () => {
    console.log(`[Cron] Running scheduled automated database backup (${config.frequency})...`);
    executeMySQLBackup({ 
      exportScope: config.exportScope, 
      tables: config.selectedTables || [] 
    });
  });
}

// Root route
app.get('/', (req, res) => {
  res.send('ChangeMe Backup Server is running successfully!');
});

// App Configuration Route for Frontend Sidebar
app.get('/api/config', (req, res) => {
  res.json({
    appName: process.env.APP_NAME || 'Sample 101',
    appVersion: process.env.APP_VERSION || '1.0.0'
  });
});

// 1. Fetch accurate table names dynamically from database
app.get('/api/tables', async (req, res) => {
  try {
    const connection = await mysql.createConnection(dbConfig);
    const [rows] = await connection.execute('SHOW TABLES');
    await connection.end();

    const tables = rows.map(row => {
      const tableNameKey = Object.keys(row)[0];
      return { name: row[tableNameKey] };
    });

    res.json({ success: true, tables });
  } catch (err) {
    console.error('Failed to fetch tables:', err.message);
    res.status(500).json({ success: false, message: 'Failed to retrieve database tables.' });
  }
});

// 2. Save Automation Settings Route & Update Cron
app.post('/api/settings', (req, res) => {
  const { autoBackup, frequency, exportScope, selectedTables } = req.body;
  
  // Update global config state
  currentAutomationConfig = {
    autoBackup,
    frequency,
    exportScope,
    selectedTables
  };

  // Reschedule the cron job with the new options
  updateCronJob(currentAutomationConfig);

  console.log(`Settings updated -> Auto: ${autoBackup}, Freq: ${frequency}, Scope: ${exportScope}`);
  res.json({ success: true, message: 'Backup settings saved and schedule updated successfully.' });
});

// 3. Manual Backup MySQL Trigger
app.post('/api/mysql', (req, res) => {
  const { exportScope, tables } = req.body || {};

  executeMySQLBackup({ exportScope, tables }, (success, result) => {
    if (success) {
      res.json({ success: true, message: `MySQL backup created: ${result}` });
    } else {
      res.status(500).json({ success: false, message: 'Database backup failed. Check server logs.' });
    }
  });
});

// Initialize default cron job on server boot
updateCronJob(currentAutomationConfig);

const PORT = process.env.PORT || 5000;
app.listen(PORT, () => {
  console.log(`Backup server running on http://localhost:${PORT}`);
});