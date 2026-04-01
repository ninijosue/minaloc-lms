# Data Warehouse Deployment Checklist

Use this checklist to ensure a smooth deployment of the data warehouse solution.

---

## Pre-Deployment Checklist

### Documentation Review
- [ ] Read `IMPLEMENTATION_SUMMARY.md`
- [ ] Review `QUICK_START.md` for deployment steps
- [ ] Understand `ARCHITECTURE_DIAGRAM.md`
- [ ] Study `QUERY_MIGRATION_GUIDE.md` (technical team)

### Environment Preparation
- [ ] Docker environment is running (`docker compose up -d`)
- [ ] Database is accessible (`docker compose exec db mariadb -uroot -p'gov@123' moodle`)
- [ ] Moodle is functional (`http://localhost:8086`)
- [ ] Admin access is available

### Backup
- [ ] Database backup created
  ```bash
  docker compose exec db mysqldump -uroot -p'gov@123' moodle > backup_$(date +%Y%m%d_%H%M%S).sql
  ```
- [ ] Backup file verified (file size > 0)
- [ ] Original `get_data.php` backed up
  ```bash
  cp elearning/blocks/realdashboard/get_data.php elearning/blocks/realdashboard/get_data_backup_$(date +%Y%m%d).php
  ```

### Team Communication
- [ ] Stakeholders notified of deployment
- [ ] Maintenance window scheduled (if applicable)
- [ ] Rollback plan communicated
- [ ] Support team on standby

---

## Deployment Steps

### Step 1: Apply Database Changes
- [ ] Access Moodle container
  ```bash
  docker compose exec moodle bash
  cd /var/www/html
  ```
- [ ] Run Moodle upgrade
  ```bash
  php admin/cli/upgrade.php --non-interactive
  ```
- [ ] Verify upgrade success (check output for errors)
- [ ] Check version updated
  ```bash
  php admin/cli/version.php | grep local_additionaluserdetails
  ```

### Step 2: Verify Table Creation
- [ ] Check table exists
  ```bash
  docker compose exec db mariadb -uroot -p'gov@123' moodle -e "SHOW TABLES LIKE 'mdl_local_reporting_status';"
  ```
- [ ] Verify table structure
  ```bash
  docker compose exec db mariadb -uroot -p'gov@123' moodle -e "DESCRIBE mdl_local_reporting_status;"
  ```
- [ ] Check indexes created
  ```bash
  docker compose exec db mariadb -uroot -p'gov@123' moodle -e "SHOW INDEX FROM mdl_local_reporting_status;"
  ```

### Step 3: Initial Data Population
- [ ] Run scheduled task manually
  ```bash
  docker compose exec moodle php admin/cli/scheduled_task.php --execute='\\local_additionaluserdetails\\task\\refresh_analytics'
  ```
- [ ] Monitor execution (should take 30-60 seconds)
- [ ] Check for errors in output
- [ ] Verify completion message displayed

### Step 4: Verify Data Integrity
- [ ] Check record count
  ```bash
  docker compose exec db mariadb -uroot -p'gov@123' moodle -e "SELECT COUNT(*) FROM mdl_local_reporting_status;"
  ```
- [ ] Verify status distribution
  ```bash
  docker compose exec db mariadb -uroot -p'gov@123' moodle -e "SELECT status, COUNT(*) FROM mdl_local_reporting_status GROUP BY status;"
  ```
- [ ] Check sample records
  ```bash
  docker compose exec db mariadb -uroot -p'gov@123' moodle -e "SELECT * FROM mdl_local_reporting_status LIMIT 10;"
  ```
- [ ] Verify no NULL status values
  ```bash
  docker compose exec db mariadb -uroot -p'gov@123' moodle -e "SELECT COUNT(*) FROM mdl_local_reporting_status WHERE status IS NULL;"
  ```
  (Should return 0)

### Step 5: Verify Scheduled Task Registration
- [ ] List scheduled tasks
  ```bash
  docker compose exec moodle php admin/cli/scheduled_task.php --list | grep refresh
  ```
- [ ] Verify task is enabled
- [ ] Check task schedule (should be: 0 2 * * *)

### Step 6: Test Optimized Queries
- [ ] Test get_data_optimized.php directly
  ```bash
  curl "http://localhost:8086/blocks/realdashboard/get_data_optimized.php" | jq .
  ```
- [ ] Verify JSON response structure
- [ ] Check response time (<5 seconds)
- [ ] Compare results with original get_data.php
  ```bash
  # Get both responses and compare
  curl "http://localhost:8086/blocks/realdashboard/get_data.php" > old_response.json
  curl "http://localhost:8086/blocks/realdashboard/get_data_optimized.php" > new_response.json
  # Compare metrics (may not be identical due to data freshness)
  ```

### Step 7: Performance Testing
- [ ] Measure old query time
  ```bash
  time curl "http://localhost:8086/blocks/realdashboard/get_data.php" > /dev/null
  ```
- [ ] Measure new query time
  ```bash
  time curl "http://localhost:8086/blocks/realdashboard/get_data_optimized.php" > /dev/null
  ```
- [ ] Verify new is significantly faster (>50% improvement)
- [ ] Test with various filters (dates, courses, locations)
- [ ] Test with edge cases (empty filters, all filters)

### Step 8: Switch to Optimized Version
- [ ] Rename files
  ```bash
  cd /var/www/html/blocks/realdashboard
  mv get_data.php get_data_legacy.php
  mv get_data_optimized.php get_data.php
  ```
- [ ] Verify file swap
  ```bash
  ls -la get_data*.php
  ```

### Step 9: Clear Caches
- [ ] Purge all caches
  ```bash
  docker compose exec moodle php admin/cli/purge_caches.php
  ```
- [ ] Verify cache cleared (check output)

### Step 10: Final Validation
- [ ] Access dashboard in browser
- [ ] Test all dashboard features
- [ ] Verify metrics display correctly
- [ ] Test all filters
- [ ] Check for JavaScript errors (browser console)
- [ ] Verify page load time (<5 seconds)

---

## Post-Deployment Verification

### Immediate (0-1 hour)
- [ ] Monitor error logs
  ```bash
  docker compose logs -f moodle | grep -i error
  ```
- [ ] Check for user-reported issues
- [ ] Verify no timeout errors
- [ ] Monitor database CPU/memory usage
- [ ] Test dashboard from multiple browsers

### Short-term (1-24 hours)
- [ ] Monitor scheduled task execution at 2 AM
- [ ] Check task logs
  ```bash
  # Via web UI: Site admin → Server → Scheduled tasks → Task log
  ```
- [ ] Verify data refreshed correctly
- [ ] Compare dashboard metrics with previous day
- [ ] Check for any anomalies

### Medium-term (1-7 days)
- [ ] Daily verification of task execution
- [ ] Monitor query performance trends
- [ ] Collect user feedback
- [ ] Check database table size growth
- [ ] Review error logs daily

---

## Rollback Procedure

If critical issues occur, follow these steps:

### Emergency Rollback (5 minutes)
- [ ] Access Moodle container
  ```bash
  docker compose exec moodle bash
  cd /var/www/html/blocks/realdashboard
  ```
- [ ] Restore original file
  ```bash
  mv get_data.php get_data_warehouse.php
  mv get_data_legacy.php get_data.php
  ```
- [ ] Clear caches
  ```bash
  php admin/cli/purge_caches.php
  ```
- [ ] Verify dashboard works
- [ ] Notify stakeholders

### Complete Rollback (if needed)
- [ ] Disable scheduled task
  ```bash
  docker compose exec moodle php admin/cli/scheduled_task.php --disable='\\local_additionaluserdetails\\task\\refresh_analytics'
  ```
- [ ] Optionally drop table
  ```bash
  docker compose exec db mariadb -uroot -p'gov@123' moodle -e "DROP TABLE IF EXISTS mdl_local_reporting_status;"
  ```
- [ ] Restore database from backup (if necessary)
  ```bash
  docker compose exec -i db mariadb -uroot -p'gov@123' moodle < backup_YYYYMMDD_HHMMSS.sql
  ```

---

## Monitoring Setup

### Daily Checks
- [ ] Task execution status
  - Site admin → Server → Scheduled tasks
  - Find: "Refresh reporting analytics data warehouse"
  - Check: Last run time and status
- [ ] Error logs review
  - Site admin → Reports → Logs
  - Filter: Errors in last 24 hours
- [ ] Data warehouse record count
  ```bash
  docker compose exec db mariadb -uroot -p'gov@123' moodle -e "SELECT COUNT(*) FROM mdl_local_reporting_status;"
  ```

### Weekly Checks
- [ ] Query performance metrics
  - Average dashboard load time
  - P95 and P99 response times
- [ ] Database table size
  ```bash
  docker compose exec db mariadb -uroot -p'gov@123' moodle -e "
  SELECT
    table_name,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
  FROM information_schema.TABLES
  WHERE table_name = 'mdl_local_reporting_status';
  "
  ```
- [ ] Task execution history
- [ ] User feedback compilation

### Monthly Checks
- [ ] Performance trend analysis
- [ ] Data accuracy validation
- [ ] Capacity planning review
- [ ] Documentation updates (if needed)

---

## Troubleshooting Guide

### Issue: Table not created
**Check:**
```bash
docker compose exec moodle php admin/cli/version.php | grep local_additionaluserdetails
```
**Solution:**
```bash
php admin/cli/upgrade.php --non-interactive
```

### Issue: Empty warehouse table
**Check:**
```bash
docker compose exec db mariadb -uroot -p'gov@123' moodle -e "SELECT COUNT(*) FROM mdl_local_reporting_status;"
```
**Solution:**
```bash
docker compose exec moodle php admin/cli/scheduled_task.php --execute='\\local_additionaluserdetails\\task\\refresh_analytics'
```

### Issue: Task not running automatically
**Check:**
```bash
docker compose exec moodle php admin/cli/scheduled_task.php --list | grep refresh
```
**Solution:**
1. Verify cron is running
2. Check task is enabled
3. Verify schedule is correct

### Issue: Dashboard still slow
**Check:**
```bash
# Verify which file is active
ls -la /var/www/html/blocks/realdashboard/get_data.php
```
**Solution:**
Ensure get_data_optimized.php is active

### Issue: Data accuracy problems
**Check:**
```bash
# Compare counts
docker compose exec db mariadb -uroot -p'gov@123' moodle -e "
SELECT
  (SELECT COUNT(*) FROM mdl_user_enrolments) as enrollments,
  (SELECT COUNT(*) FROM mdl_local_reporting_status) as warehouse;
"
```
**Solution:**
Re-run task manually and investigate discrepancies

---

## Success Criteria

### Technical Success
- [ ] Dashboard load time <5 seconds
- [ ] Zero timeout errors
- [ ] Scheduled task runs successfully daily
- [ ] Data warehouse updates correctly
- [ ] All metrics calculate accurately

### User Experience Success
- [ ] Users report faster dashboard
- [ ] No complaints about timeouts
- [ ] All filters work correctly
- [ ] Data appears accurate
- [ ] No new bugs introduced

### Operational Success
- [ ] Task runs automatically without intervention
- [ ] Minimal maintenance required
- [ ] Easy to monitor and troubleshoot
- [ ] Documentation is clear and complete
- [ ] Team understands the system

---

## Sign-off

### Deployment Team
- [ ] **Technical Lead**: _____________________ Date: _____
- [ ] **Database Admin**: _____________________ Date: _____
- [ ] **QA Lead**: ___________________________ Date: _____

### Stakeholders
- [ ] **Product Owner**: _____________________ Date: _____
- [ ] **Operations**: ________________________ Date: _____

---

## Notes and Observations

```
Date: __________

Deployment Notes:
__________________________________________________________________
__________________________________________________________________
__________________________________________________________________

Issues Encountered:
__________________________________________________________________
__________________________________________________________________
__________________________________________________________________

Resolution:
__________________________________________________________________
__________________________________________________________________
__________________________________________________________________

Performance Metrics:
- Old dashboard load time: _______
- New dashboard load time: _______
- Improvement: _______%

Next Steps:
__________________________________________________________________
__________________________________________________________________
__________________________________________________________________
```

---

## References

- Full Documentation: `DATA_WAREHOUSE_DEPLOYMENT.md`
- Quick Start: `QUICK_START.md`
- Architecture: `ARCHITECTURE_DIAGRAM.md`
- Query Migration: `QUERY_MIGRATION_GUIDE.md`
- Summary: `IMPLEMENTATION_SUMMARY.md`
