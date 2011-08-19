/*********************************************************************
Copyright (C) 2011 Hewlett-Packard Development Company, L.P.

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
version 2 as published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*********************************************************************/

#include <stdio.h>
#include <stdlib.h>
#include <assert.h>
#include "CUnit/CUnit.h"
#include "CUnit/Automated.h"
#include "testRun.h"

/**
 * \file testRun.c
 * \brief main function for in this testing module
 */

extern CU_SuiteInfo suites[];

/**
 * \brief initialize db
 */
int DelagentDBInit()
{
  char CMD[256];
  int rc;
   
  memset(CMD, '\0', sizeof(CMD));
  sprintf(CMD, "su fossy -c 'sh testInitDB.sh'");
  rc = system(CMD); 
  if (rc != 0)
  {
    printf("Database initialize ERROR!\n");
    return -1; 
  }

  return 0;
}
/**
 * \brief clean db
 */
int DelagentDBClean()
{
  char CMD[256];
  int rc;

  memset(CMD, '\0', sizeof(CMD));
  sprintf(CMD, "su fossy -c 'sh testCleanDB.sh'");
  rc = system(CMD);
  if (rc != 0)
  {
    printf("Database clean ERROR!\n");
    return -1;
  }

  return 0;
}

/**
 * \brief init db and repo
 */
int DelagentInit()
{
  char CMD[256];
  int rc;

  if (DelagentDBInit()!=0) return -1;

  memset(CMD, '\0', sizeof(CMD));
  sprintf(CMD, "su fossy -c 'sh testInitRepo.sh'");
  rc = system(CMD);
  if (rc != 0)
  {
    printf("Repository Init ERROR!\n");
    DelagentDBClean();
    return -1;
  }

  return 0;
}

/**
 * \brief clean db and repo
 */
int DelagentClean()
{
  char CMD[256];
  int rc;

  if (DelagentDBClean()!=0) return -1;

  memset(CMD, '\0', sizeof(CMD));
  sprintf(CMD, "su fossy -c 'sh testCleanRepo.sh'");
  rc = system(CMD);
  if (rc != 0)
  {
    printf("Repository Clean ERROR!\n");
    return -1;
  }

  return 0;
}


void AddTests(void)
{
  assert(NULL != CU_get_registry());
  assert(!CU_is_test_running());


  if (CUE_SUCCESS != CU_register_suites(suites))
  {
    fprintf(stderr, "Register suites failed - %s ", CU_get_error_msg());
    exit(EXIT_FAILURE);
  }
}

/**
 * \brief  main test function
 */
int main( int argc, char *argv[] )
{
  printf("Test Start\n");
  if (CU_initialize_registry())
  {

    fprintf(stderr, "\nInitialization of Test Registry failed.\n");
    exit(EXIT_FAILURE);
  } else
  {
    AddTests();
    /** delagent */
    CU_set_output_filename("delagent");
    CU_list_tests_to_file();
    CU_automated_run_tests();
    //CU_cleanup_registry();
  }
  printf("Test End\n");
  printf("Results:\n");
  printf("  Number of suites run: %d\n", CU_get_number_of_suites_run());
  printf("  Number of tests run: %d\n", CU_get_number_of_tests_run());
  printf("  Number of tests failed: %d\n", CU_get_number_of_tests_failed());
  printf("  Number of asserts: %d\n", CU_get_number_of_asserts());
  printf("  Number of successes: %d\n", CU_get_number_of_successes());
  printf("  Number of failures: %d\n", CU_get_number_of_failures());
  CU_cleanup_registry();
  return 0;
}

