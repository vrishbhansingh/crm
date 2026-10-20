<#
  Registers a Windows Task Scheduler job that runs Laravel's scheduler every
  minute, which is what drives task/lead reminders, scheduled campaigns and
  the queue worker. Run once, from an elevated PowerShell:

      powershell -ExecutionPolicy Bypass -File scripts\install-scheduler.ps1

  Optional: -Php "C:\wamp64\bin\php\php8.2.0\php.exe" to pick a PHP build.
  Remove with: Unregister-ScheduledTask -TaskName "CRM Scheduler" -Confirm:$false
#>
param(
    [string]$Php = "C:\wamp64\bin\php\php8.2.0\php.exe",
    [string]$TaskName = "CRM Scheduler"
)

$project = Split-Path -Parent $PSScriptRoot
if (-not (Test-Path $Php)) { throw "PHP not found at $Php - pass -Php with the right path." }

$action = New-ScheduledTaskAction -Execute $Php -Argument "artisan schedule:run" -WorkingDirectory $project
$trigger = New-ScheduledTaskTrigger -Once -At (Get-Date).AddMinutes(1) -RepetitionInterval (New-TimeSpan -Minutes 1)
$settings = New-ScheduledTaskSettingsSet -StartWhenAvailable -MultipleInstances IgnoreNew -ExecutionTimeLimit (New-TimeSpan -Minutes 5)

Register-ScheduledTask -TaskName $TaskName -Action $action -Trigger $trigger -Settings $settings -Description "Runs the CRM Laravel scheduler every minute" -Force | Out-Null
Write-Host "Registered '$TaskName' (every minute) for $project using $Php"
