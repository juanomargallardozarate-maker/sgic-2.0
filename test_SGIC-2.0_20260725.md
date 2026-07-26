```
C:\laragon\www\sgic-2.0(master -> origin)
λ php artisan test
   PASS  Tests\Unit\ExampleTest
   that true is true✓
1.18s
   FAIL  Tests\Feature\Auth\AuthenticationTest
   login screen can be rendered⨯
   users can authenticate using the login screen⨯
   users can not authenticate with invalid password⨯
   users can logout⨯
   FAIL  Tests\Feature\Auth\EmailVerificationTest
   email verification screen can be rendered⨯
   email can be verified⨯
   email is not verified with invalid hash⨯
```

```
   FAIL  Tests\Feature\Auth\PasswordConfirmationTest
   confirm password screen can be rendered⨯
   password can be confirmed⨯
   password is not confirmed with invalid password⨯
```

```
   FAIL  Tests\Feature\Auth\PasswordResetTest
   reset password link screen can be rendered⨯
   reset password link can be requested⨯
   reset password screen can be rendered⨯
   password can be reset with valid token⨯
```

```
   FAIL  Tests\Feature\Auth\PasswordUpdateTest
   password can be updated⨯
   correct password must be provided to update password⨯
```

```
   FAIL  Tests\Feature\Auth\RegistrationTest
   registration screen can be rendered⨯
   new users can register⨯
```

```
   PASS  Tests\Feature\ExampleTest
   the application returns a successful response✓
2.03s
```

```
   FAIL  Tests\Feature\ProfileTest
   profile page is displayed⨯
   profile information can be updated⨯
   email verification status is unchanged when the email address is unchanged⨯
   user can delete their account⨯
   correct password must be provided to delete account⨯
```

```
────────────────────────────────────────────────────────────────────────────────
────────────────────────────────────────────────────────────────────────────────
────
   FAILED  Tests\Feature\Auth\AuthenticationTest > login screen can be rendered
QueryException
  SQLSTATE[HY000]: General error: 1553 Cannot drop index
'beneficiaries_contract_id_customer_id_unique': needed in a foreign key
constraint (Connection: mysql, SQL: alt
er table `beneficiaries` drop index
`beneficiaries_contract_id_customer_id_unique`)
  at vendor\laravel\framework\src\Illuminate\Database\Connection.php:825
    821                     $this->getName(), $query, $this-▕
>prepareBindings($bindings), $e
    822                 );▕
    823             }▕
```

```
    824▕
   825             throw new QueryException(➜▕
    826                 $this->getName(), $query, $this-▕
>prepareBindings($bindings), $e
    827             );▕
    828         }▕
    829     }▕
```

```
  1   vendor\laravel\framework\src\Illuminate\Database\Connection.php:825
```

```
  2   vendor\laravel\framework\src\Illuminate\Database\Connection.php:571
      NunoMaduro\Collision\Exceptions\TestException::("SQLSTATE[HY000]: General
error: 1553 Cannot drop index 'beneficiaries_contract_id_customer_id_unique':
needed in
```

```
a foreign key constraint")
```

```
────────────────────────────────────────────────────────────────────────────────
```

```
────────────────────────────────────────────────────────────────────────────────
```

```
────
   FAILED  Tests\Feature\Auth\AuthenticationTest > users can authenticate using
the login screen
QueryException
```

```
  SQLSTATE[HY000]: General error: 1553 Cannot drop index
'beneficiaries_contract_id_customer_id_unique': needed in a foreign key
constraint (Connection: mysql, SQL: alt
er table `beneficiaries` drop index
`beneficiaries_contract_id_customer_id_unique`)
```

```
  at vendor\laravel\framework\src\Illuminate\Database\Connection.php:825
    821                     $this->getName(), $query, $this-▕
>prepareBindings($bindings), $e
    822                 );▕
    823             }▕
    824▕
   825             throw new QueryException(➜▕
    826                 $this->getName(), $query, $this-▕
>prepareBindings($bindings), $e
    827             );▕
    828         }▕
    829     }▕
```

```
  1   vendor\laravel\framework\src\Illuminate\Database\Connection.php:825
```

```
  2   vendor\laravel\framework\src\Illuminate\Database\Connection.php:571
      NunoMaduro\Collision\Exceptions\TestException::("SQLSTATE[HY000]: General
error: 1553 Cannot drop index 'beneficiaries_contract_id_customer_id_unique':
needed in
```

```
a foreign key constraint")
```

```
────────────────────────────────────────────────────────────────────────────────
```

```
────────────────────────────────────────────────────────────────────────────────
```

```
────
```

```
   FAILED  Tests\Feature\Auth\AuthenticationTest > users can not authenticate
with invalid password
QueryException
  SQLSTATE[HY000]: General error: 1553 Cannot drop index
'beneficiaries_contract_id_customer_id_unique': needed in a foreign key
constraint (Connection: mysql, SQL: alt
er table `beneficiaries` drop index
`beneficiaries_contract_id_customer_id_unique`)
```

```
  at vendor\laravel\framework\src\Illuminate\Database\Connection.php:825
```

```
    821                     $this->getName(), $query, $this-▕
>prepareBindings($bindings), $e
    822                 );▕
    823             }▕
    824▕
   825             throw new QueryException(➜▕
    826                 $this->getName(), $query, $this-▕
>prepareBindings($bindings), $e
    827             );▕
    828         }▕
    829     }▕
```

```
  1   vendor\laravel\framework\src\Illuminate\Database\Connection.php:825
```

```
  2   vendor\laravel\framework\src\Illuminate\Database\Connection.php:571
      NunoMaduro\Collision\Exceptions\TestException::("SQLSTATE[HY000]: General
error: 1553 Cannot drop index 'beneficiaries_contract_id_customer_id_unique':
needed in
```

```
a foreign key constraint")
```

```
────────────────────────────────────────────────────────────────────────────────
```

```
────────────────────────────────────────────────────────────────────────────────
```

```
────
   FAILED  Tests\Feature\Auth\AuthenticationTest > users can logout
QueryException
  SQLSTATE[HY000]: General error: 1553 Cannot drop index
'beneficiaries_contract_id_customer_id_unique': needed in a foreign key
constraint (Connection: mysql, SQL: alt
er table `beneficiaries` drop index
`beneficiaries_contract_id_customer_id_unique`)
```

```
  at vendor\laravel\framework\src\Illuminate\Database\Connection.php:825
    821                     $this->getName(), $query, $this-▕
>prepareBindings($bindings), $e
    822                 );▕
    823             }▕
    824▕
   825             throw new QueryException(➜▕
    826                 $this->getName(), $query, $this-▕
>prepareBindings($bindings), $e
    827             );▕
    828         }▕
    829     }▕
```

```
  1   vendor\laravel\framework\src\Illuminate\Database\Connection.php:825
```

```
  2   vendor\laravel\framework\src\Illuminate\Database\Connection.php:571
      NunoMaduro\Collision\Exceptions\TestException::("SQLSTATE[HY000]: General
error: 1553 Cannot drop index 'beneficiaries_contract_id_customer_id_unique':
needed in
```

```
a foreign key constraint")
```

```
────────────────────────────────────────────────────────────────────────────────
```

```
────────────────────────────────────────────────────────────────────────────────
```

```
────
   FAILED  Tests\Feature\Auth\EmailVerificationTest > email verification screen
can be rendered
QueryException
  SQLSTATE[HY000]: General error: 1553 Cannot drop index
'beneficiaries_contract_id_customer_id_unique': needed in a foreign key
constraint (Connection: mysql, SQL: alt
er table `beneficiaries` drop index
```

```
`beneficiaries_contract_id_customer_id_unique`)
```

```
  at vendor\laravel\framework\src\Illuminate\Database\Connection.php:825
    821                     $this->getName(), $query, $this-▕
>prepareBindings($bindings), $e
    822                 );▕
    823             }▕
    824▕
   825             throw new QueryException(➜▕
    826                 $this->getName(), $query, $this-▕
>prepareBindings($bindings), $e
    827             );▕
    828         }▕
    829     }▕
```

```
  1   vendor\laravel\framework\src\Illuminate\Database\Connection.php:825
```

```
  2   vendor\laravel\framework\src\Illuminate\Database\Connection.php:571
      NunoMaduro\Collision\Exceptions\TestException::("SQLSTATE[HY000]: General
error: 1553 Cannot drop index 'beneficiaries_contract_id_customer_id_unique':
needed in
```

```
a foreign key constraint")
```

```
────────────────────────────────────────────────────────────────────────────────
```

```
────────────────────────────────────────────────────────────────────────────────
```

```
────
   FAILED  Tests\Feature\Auth\EmailVerificationTest > email can be verified
QueryException
  SQLSTATE[HY000]: General error: 1553 Cannot drop index
'beneficiaries_contract_id_customer_id_unique': needed in a foreign key
constraint (Connection: mysql, SQL: alt
er table `beneficiaries` drop index
`beneficiaries_contract_id_customer_id_unique`)
  at vendor\laravel\framework\src\Illuminate\Database\Connection.php:825
    821                     $this->getName(), $query, $this-▕
>prepareBindings($bindings), $e
    822                 );▕
    823             }▕
    824▕
   825             throw new QueryException(➜▕
    826                 $this->getName(), $query, $this-▕
>prepareBindings($bindings), $e
    827             );▕
    828         }▕
    829     }▕
```

```
  1   vendor\laravel\framework\src\Illuminate\Database\Connection.php:825
```

```
  2   vendor\laravel\framework\src\Illuminate\Database\Connection.php:571
      NunoMaduro\Collision\Exceptions\TestException::("SQLSTATE[HY000]: General
error: 1553 Cannot drop index 'beneficiaries_contract_id_customer_id_unique':
needed in
```

```
a foreign key constraint")
```

```
────────────────────────────────────────────────────────────────────────────────
────────────────────────────────────────────────────────────────────────────────
```

```
────
```

```
   FAILED  Tests\Feature\Auth\EmailVerificationTest > email is not verified with
invalid hash
QueryException
  SQLSTATE[HY000]: General error: 1553 Cannot drop index
```

```
'beneficiaries_contract_id_customer_id_unique': needed in a foreign key
constraint (Connection: mysql, SQL: alt
er table `beneficiaries` drop index
`beneficiaries_contract_id_customer_id_unique`)
```

```
  at vendor\laravel\framework\src\Illuminate\Database\Connection.php:825
    821                     $this->getName(), $query, $this-▕
>prepareBindings($bindings), $e
    822                 );▕
    823             }▕
    824▕
   825             throw new QueryException(➜▕
    826                 $this->getName(), $query, $this-▕
>prepareBindings($bindings), $e
    827             );▕
    828         }▕
    829     }▕
```

```
  1   vendor\laravel\framework\src\Illuminate\Database\Connection.php:825
```

```
  2   vendor\laravel\framework\src\Illuminate\Database\Connection.php:571
      NunoMaduro\Collision\Exceptions\TestException::("SQLSTATE[HY000]: General
error: 1553 Cannot drop index 'beneficiaries_contract_id_customer_id_unique':
needed in
```

```
a foreign key constraint")
```

```
────────────────────────────────────────────────────────────────────────────────
```

```
────────────────────────────────────────────────────────────────────────────────
```

```
────
   FAILED  Tests\Feature\Auth\PasswordConfirmationTest > confirm password screen
can be rendered
QueryException
  SQLSTATE[HY000]: General error: 1553 Cannot drop index
'beneficiaries_contract_id_customer_id_unique': needed in a foreign key
constraint (Connection: mysql, SQL: alt
er table `beneficiaries` drop index
`beneficiaries_contract_id_customer_id_unique`)
```

```
  at vendor\laravel\framework\src\Illuminate\Database\Connection.php:825
    821                     $this->getName(), $query, $this-▕
>prepareBindings($bindings), $e
    822                 );▕
    823             }▕
    824▕
   825             throw new QueryException(➜▕
    826                 $this->getName(), $query, $this-▕
>prepareBindings($bindings), $e
    827             );▕
    828         }▕
    829     }▕
```

```
  1   vendor\laravel\framework\src\Illuminate\Database\Connection.php:825
```

```
  2   vendor\laravel\framework\src\Illuminate\Database\Connection.php:571
      NunoMaduro\Collision\Exceptions\TestException::("SQLSTATE[HY000]: General
error: 1553 Cannot drop index 'beneficiaries_contract_id_customer_id_unique':
needed in
```

```
a foreign key constraint")
```

```
────────────────────────────────────────────────────────────────────────────────
```

```
────────────────────────────────────────────────────────────────────────────────
```

```
────
```

```
   FAILED  Tests\Feature\Auth\PasswordConfirmationTest > password can be
confirmed
QueryException
  SQLSTATE[HY000]: General error: 1553 Cannot drop index
'beneficiaries_contract_id_customer_id_unique': needed in a foreign key
constraint (Connection: mysql, SQL: alt
er table `beneficiaries` drop index
`beneficiaries_contract_id_customer_id_unique`)
  at vendor\laravel\framework\src\Illuminate\Database\Connection.php:825
    821                     $this->getName(), $query, $this-▕
>prepareBindings($bindings), $e
    822                 );▕
    823             }▕
    824▕
   825             throw new QueryException(➜▕
    826                 $this->getName(), $query, $this-▕
>prepareBindings($bindings), $e
    827             );▕
    828         }▕
    829     }▕
```

```
  1   vendor\laravel\framework\src\Illuminate\Database\Connection.php:825
  2   vendor\laravel\framework\src\Illuminate\Database\Connection.php:571
      NunoMaduro\Collision\Exceptions\TestException::("SQLSTATE[HY000]: General
error: 1553 Cannot drop index 'beneficiaries_contract_id_customer_id_unique':
needed in
```

```
a foreign key constraint")
```

```
────────────────────────────────────────────────────────────────────────────────
────────────────────────────────────────────────────────────────────────────────
────
   FAILED  Tests\Feature\Auth\PasswordConfirmationTest > password is not
confirmed with invalid password
QueryException
  SQLSTATE[HY000]: General error: 1553 Cannot drop index
'beneficiaries_contract_id_customer_id_unique': needed in a foreign key
constraint (Connection: mysql, SQL: alt
er table `beneficiaries` drop index
`beneficiaries_contract_id_customer_id_unique`)
  at vendor\laravel\framework\src\Illuminate\Database\Connection.php:825
    821                     $this->getName(), $query, $this-▕
>prepareBindings($bindings), $e
    822                 );▕
    823             }▕
    824▕
   825             throw new QueryException(➜▕
    826                 $this->getName(), $query, $this-▕
>prepareBindings($bindings), $e
    827             );▕
    828         }▕
    829     }▕
```

```
  1   vendor\laravel\framework\src\Illuminate\Database\Connection.php:825
```

```
  2   vendor\laravel\framework\src\Illuminate\Database\Connection.php:571
      NunoMaduro\Collision\Exceptions\TestException::("SQLSTATE[HY000]: General
error: 1553 Cannot drop index 'beneficiaries_contract_id_customer_id_unique':
needed in
```

```
a foreign key constraint")
```

```
────────────────────────────────────────────────────────────────────────────────
────────────────────────────────────────────────────────────────────────────────
```

```
────
```

```
   FAILED  Tests\Feature\Auth\PasswordResetTest > reset password link screen can
be rendered
QueryException
```

```
  SQLSTATE[HY000]: General error: 1553 Cannot drop index
'beneficiaries_contract_id_customer_id_unique': needed in a foreign key
constraint (Connection: mysql, SQL: alt
er table `beneficiaries` drop index
`beneficiaries_contract_id_customer_id_unique`)
```

```
  at vendor\laravel\framework\src\Illuminate\Database\Connection.php:825
    821                     $this->getName(), $query, $this-▕
>prepareBindings($bindings), $e
    822                 );▕
    823             }▕
    824▕
   825             throw new QueryException(➜▕
    826                 $this->getName(), $query, $this-▕
>prepareBindings($bindings), $e
    827             );▕
    828         }▕
    829     }▕
```

```
  1   vendor\laravel\framework\src\Illuminate\Database\Connection.php:825
```

```
  2   vendor\laravel\framework\src\Illuminate\Database\Connection.php:571
      NunoMaduro\Collision\Exceptions\TestException::("SQLSTATE[HY000]: General
error: 1553 Cannot drop index 'beneficiaries_contract_id_customer_id_unique':
needed in
```

```
a foreign key constraint")
```

```
────────────────────────────────────────────────────────────────────────────────
```

```
────────────────────────────────────────────────────────────────────────────────
```

```
────
   FAILED  Tests\Feature\Auth\PasswordResetTest > reset password link can be
requested
QueryException
```

```
  SQLSTATE[HY000]: General error: 1553 Cannot drop index
```

```
'beneficiaries_contract_id_customer_id_unique': needed in a foreign key
constraint (Connection: mysql, SQL: alt
er table `beneficiaries` drop index
`beneficiaries_contract_id_customer_id_unique`)
```

```
  at vendor\laravel\framework\src\Illuminate\Database\Connection.php:825
    821                     $this->getName(), $query, $this-▕
>prepareBindings($bindings), $e
    822                 );▕
    823             }▕
    824▕
   825             throw new QueryException(➜▕
    826                 $this->getName(), $query, $this-▕
>prepareBindings($bindings), $e
    827             );▕
    828         }▕
    829     }▕
```

- `1   vendor\laravel\framework\src\Illuminate\Database\Connection.php:825` 

- `2   vendor\laravel\framework\src\Illuminate\Database\Connection.php:571 NunoMaduro\Collision\Exceptions\TestException::("SQLSTATE[HY000]: General` 

```
error: 1553 Cannot drop index 'beneficiaries_contract_id_customer_id_unique':
needed in
a foreign key constraint")
```

```
────────────────────────────────────────────────────────────────────────────────
────────────────────────────────────────────────────────────────────────────────
────
   FAILED  Tests\Feature\Auth\PasswordResetTest > reset password screen can be
rendered
QueryException
  SQLSTATE[HY000]: General error: 1553 Cannot drop index
'beneficiaries_contract_id_customer_id_unique': needed in a foreign key
constraint (Connection: mysql, SQL: alt
er table `beneficiaries` drop index
`beneficiaries_contract_id_customer_id_unique`)
  at vendor\laravel\framework\src\Illuminate\Database\Connection.php:825
    821                     $this->getName(), $query, $this-▕
>prepareBindings($bindings), $e
    822                 );▕
    823             }▕
    824▕
   825             throw new QueryException(➜▕
    826                 $this->getName(), $query, $this-▕
>prepareBindings($bindings), $e
    827             );▕
    828         }▕
    829     }▕
```

```
  1   vendor\laravel\framework\src\Illuminate\Database\Connection.php:825
```

```
  2   vendor\laravel\framework\src\Illuminate\Database\Connection.php:571
      NunoMaduro\Collision\Exceptions\TestException::("SQLSTATE[HY000]: General
error: 1553 Cannot drop index 'beneficiaries_contract_id_customer_id_unique':
needed in
a foreign key constraint")
```

```
────────────────────────────────────────────────────────────────────────────────
────────────────────────────────────────────────────────────────────────────────
────
   FAILED  Tests\Feature\Auth\PasswordResetTest > password can be reset with
valid token
QueryException
  SQLSTATE[HY000]: General error: 1553 Cannot drop index
'beneficiaries_contract_id_customer_id_unique': needed in a foreign key
constraint (Connection: mysql, SQL: alt
er table `beneficiaries` drop index
`beneficiaries_contract_id_customer_id_unique`)
  at vendor\laravel\framework\src\Illuminate\Database\Connection.php:825
    821                     $this->getName(), $query, $this-▕
>prepareBindings($bindings), $e
    822                 );▕
    823             }▕
    824▕
   825             throw new QueryException(➜▕
    826                 $this->getName(), $query, $this-▕
>prepareBindings($bindings), $e
    827             );▕
    828         }▕
    829     }▕
```

- `1   vendor\laravel\framework\src\Illuminate\Database\Connection.php:825` 

```
  2   vendor\laravel\framework\src\Illuminate\Database\Connection.php:571
      NunoMaduro\Collision\Exceptions\TestException::("SQLSTATE[HY000]: General
error: 1553 Cannot drop index 'beneficiaries_contract_id_customer_id_unique':
needed in
```

```
a foreign key constraint")
```

```
────────────────────────────────────────────────────────────────────────────────
```

```
────────────────────────────────────────────────────────────────────────────────
```

```
────
   FAILED  Tests\Feature\Auth\PasswordUpdateTest > password can be updated
QueryException
  SQLSTATE[HY000]: General error: 1553 Cannot drop index
'beneficiaries_contract_id_customer_id_unique': needed in a foreign key
constraint (Connection: mysql, SQL: alt
er table `beneficiaries` drop index
`beneficiaries_contract_id_customer_id_unique`)
  at vendor\laravel\framework\src\Illuminate\Database\Connection.php:825
    821                     $this->getName(), $query, $this-▕
>prepareBindings($bindings), $e
    822                 );▕
    823             }▕
    824▕
   825             throw new QueryException(➜▕
    826                 $this->getName(), $query, $this-▕
>prepareBindings($bindings), $e
    827             );▕
    828         }▕
    829     }▕
```

```
  1   vendor\laravel\framework\src\Illuminate\Database\Connection.php:825
```

```
  2   vendor\laravel\framework\src\Illuminate\Database\Connection.php:571
      NunoMaduro\Collision\Exceptions\TestException::("SQLSTATE[HY000]: General
error: 1553 Cannot drop index 'beneficiaries_contract_id_customer_id_unique':
needed in
```

```
a foreign key constraint")
```

```
────────────────────────────────────────────────────────────────────────────────
```

```
────────────────────────────────────────────────────────────────────────────────
```

```
────
   FAILED  Tests\Feature\Auth\PasswordUpdateTest > correct password must be
provided to update password
QueryException
  SQLSTATE[HY000]: General error: 1553 Cannot drop index
'beneficiaries_contract_id_customer_id_unique': needed in a foreign key
constraint (Connection: mysql, SQL: alt
er table `beneficiaries` drop index
`beneficiaries_contract_id_customer_id_unique`)
  at vendor\laravel\framework\src\Illuminate\Database\Connection.php:825
    821                     $this->getName(), $query, $this-▕
>prepareBindings($bindings), $e
    822                 );▕
    823             }▕
    824▕
   825             throw new QueryException(➜▕
    826                 $this->getName(), $query, $this-▕
>prepareBindings($bindings), $e
    827             );▕
```

```
▕
▕
```

- `1   vendor\laravel\framework\src\Illuminate\Database\Connection.php:825` 

- `2   vendor\laravel\framework\src\Illuminate\Database\Connection.php:571 NunoMaduro\Collision\Exceptions\TestException::("SQLSTATE[HY000]: General error: 1553 Cannot drop index 'beneficiaries_contract_id_customer_id_unique': needed in` 

```
a foreign key constraint")
```

```
────────────────────────────────────────────────────────────────────────────────
```

```
────────────────────────────────────────────────────────────────────────────────
```

```
────
   FAILED  Tests\Feature\Auth\RegistrationTest > registration screen can be
rendered
QueryException
  SQLSTATE[HY000]: General error: 1553 Cannot drop index
'beneficiaries_contract_id_customer_id_unique': needed in a foreign key
constraint (Connection: mysql, SQL: alt
er table `beneficiaries` drop index
`beneficiaries_contract_id_customer_id_unique`)
  at vendor\laravel\framework\src\Illuminate\Database\Connection.php:825
    821                     $this->getName(), $query, $this-▕
>prepareBindings($bindings), $e
    822                 );▕
    823             }▕
    824▕
   825             throw new QueryException(➜▕
    826                 $this->getName(), $query, $this-▕
>prepareBindings($bindings), $e
    827             );▕
    828         }▕
    829     }▕
```

```
  1   vendor\laravel\framework\src\Illuminate\Database\Connection.php:825
```

```
  2   vendor\laravel\framework\src\Illuminate\Database\Connection.php:571
      NunoMaduro\Collision\Exceptions\TestException::("SQLSTATE[HY000]: General
error: 1553 Cannot drop index 'beneficiaries_contract_id_customer_id_unique':
needed in
```

```
a foreign key constraint")
```

```
────────────────────────────────────────────────────────────────────────────────
```

```
────────────────────────────────────────────────────────────────────────────────
```

```
────
   FAILED  Tests\Feature\Auth\RegistrationTest > new users can register
QueryException
  SQLSTATE[HY000]: General error: 1553 Cannot drop index
'beneficiaries_contract_id_customer_id_unique': needed in a foreign key
constraint (Connection: mysql, SQL: alt
er table `beneficiaries` drop index
`beneficiaries_contract_id_customer_id_unique`)
```

```
  at vendor\laravel\framework\src\Illuminate\Database\Connection.php:825
    821                     $this->getName(), $query, $this-▕
>prepareBindings($bindings), $e
    822                 );▕
    823             }▕
    824▕
   825             throw new QueryException(➜▕
```

```
    826                 $this->getName(), $query, $this-▕
>prepareBindings($bindings), $e
    827             );▕
    828         }▕
    829     }▕
```

```
  1   vendor\laravel\framework\src\Illuminate\Database\Connection.php:825
```

```
  2   vendor\laravel\framework\src\Illuminate\Database\Connection.php:571
      NunoMaduro\Collision\Exceptions\TestException::("SQLSTATE[HY000]: General
error: 1553 Cannot drop index 'beneficiaries_contract_id_customer_id_unique':
needed in
```

```
a foreign key constraint")
```

```
────────────────────────────────────────────────────────────────────────────────
```

```
────────────────────────────────────────────────────────────────────────────────
```

```
────
   FAILED  Tests\Feature\ProfileTest > profile page is displayed
QueryException
  SQLSTATE[HY000]: General error: 1553 Cannot drop index
'beneficiaries_contract_id_customer_id_unique': needed in a foreign key
constraint (Connection: mysql, SQL: alt
er table `beneficiaries` drop index
`beneficiaries_contract_id_customer_id_unique`)
  at vendor\laravel\framework\src\Illuminate\Database\Connection.php:825
    821                     $this->getName(), $query, $this-▕
>prepareBindings($bindings), $e
    822                 );▕
    823             }▕
    824▕
   825             throw new QueryException(➜▕
    826                 $this->getName(), $query, $this-▕
>prepareBindings($bindings), $e
    827             );▕
    828         }▕
    829     }▕
```

```
  1   vendor\laravel\framework\src\Illuminate\Database\Connection.php:825
```

```
  2   vendor\laravel\framework\src\Illuminate\Database\Connection.php:571
      NunoMaduro\Collision\Exceptions\TestException::("SQLSTATE[HY000]: General
error: 1553 Cannot drop index 'beneficiaries_contract_id_customer_id_unique':
needed in
```

```
a foreign key constraint")
```

```
────────────────────────────────────────────────────────────────────────────────
```

```
────────────────────────────────────────────────────────────────────────────────
```

```
────
   FAILED  Tests\Feature\ProfileTest > profile information can be updated
QueryException
  SQLSTATE[HY000]: General error: 1553 Cannot drop index
'beneficiaries_contract_id_customer_id_unique': needed in a foreign key
constraint (Connection: mysql, SQL: alt
er table `beneficiaries` drop index
`beneficiaries_contract_id_customer_id_unique`)
  at vendor\laravel\framework\src\Illuminate\Database\Connection.php:825
    821                     $this->getName(), $query, $this-▕
>prepareBindings($bindings), $e
    822                 );▕
    823             }▕
```

```
    824▕
   825             throw new QueryException(➜▕
    826                 $this->getName(), $query, $this-▕
>prepareBindings($bindings), $e
    827             );▕
    828         }▕
    829     }▕
```

```
  1   vendor\laravel\framework\src\Illuminate\Database\Connection.php:825
```

```
  2   vendor\laravel\framework\src\Illuminate\Database\Connection.php:571
      NunoMaduro\Collision\Exceptions\TestException::("SQLSTATE[HY000]: General
error: 1553 Cannot drop index 'beneficiaries_contract_id_customer_id_unique':
needed in
```

```
a foreign key constraint")
```

```
────────────────────────────────────────────────────────────────────────────────
```

```
────────────────────────────────────────────────────────────────────────────────
```

```
────
   FAILED  Tests\Feature\ProfileTest > email verification status is unchanged
when the email address is unchanged
QueryException
```

```
  SQLSTATE[HY000]: General error: 1553 Cannot drop index
'beneficiaries_contract_id_customer_id_unique': needed in a foreign key
constraint (Connection: mysql, SQL: alt
er table `beneficiaries` drop index
`beneficiaries_contract_id_customer_id_unique`)
```

```
  at vendor\laravel\framework\src\Illuminate\Database\Connection.php:825
    821                     $this->getName(), $query, $this-▕
>prepareBindings($bindings), $e
    822                 );▕
    823             }▕
    824▕
   825             throw new QueryException(➜▕
    826                 $this->getName(), $query, $this-▕
>prepareBindings($bindings), $e
    827             );▕
    828         }▕
    829     }▕
```

```
  1   vendor\laravel\framework\src\Illuminate\Database\Connection.php:825
```

```
  2   vendor\laravel\framework\src\Illuminate\Database\Connection.php:571
      NunoMaduro\Collision\Exceptions\TestException::("SQLSTATE[HY000]: General
error: 1553 Cannot drop index 'beneficiaries_contract_id_customer_id_unique':
needed in
```

```
a foreign key constraint")
```

```
────────────────────────────────────────────────────────────────────────────────
```

```
────────────────────────────────────────────────────────────────────────────────
```

```
────
   FAILED  Tests\Feature\ProfileTest > user can delete their account
QueryException
  SQLSTATE[HY000]: General error: 1553 Cannot drop index
'beneficiaries_contract_id_customer_id_unique': needed in a foreign key
constraint (Connection: mysql, SQL: alt
er table `beneficiaries` drop index
`beneficiaries_contract_id_customer_id_unique`)
```

```
  at vendor\laravel\framework\src\Illuminate\Database\Connection.php:825
    821                     $this->getName(), $query, $this-▕
```

```
>prepareBindings($bindings), $e
    822                 );▕
    823             }▕
    824▕
   825             throw new QueryException(➜▕
    826                 $this->getName(), $query, $this-▕
>prepareBindings($bindings), $e
    827             );▕
    828         }▕
    829     }▕
```

```
  1   vendor\laravel\framework\src\Illuminate\Database\Connection.php:825
```

```
  2   vendor\laravel\framework\src\Illuminate\Database\Connection.php:571
      NunoMaduro\Collision\Exceptions\TestException::("SQLSTATE[HY000]: General
error: 1553 Cannot drop index 'beneficiaries_contract_id_customer_id_unique':
needed in
```

```
a foreign key constraint")
```

```
────────────────────────────────────────────────────────────────────────────────
```

```
────────────────────────────────────────────────────────────────────────────────
```

```
────
   FAILED  Tests\Feature\ProfileTest > correct password must be provided to
delete account
QueryException
  SQLSTATE[HY000]: General error: 1553 Cannot drop index
'beneficiaries_contract_id_customer_id_unique': needed in a foreign key
constraint (Connection: mysql, SQL: alt
er table `beneficiaries` drop index
`beneficiaries_contract_id_customer_id_unique`)
  at vendor\laravel\framework\src\Illuminate\Database\Connection.php:825
    821                     $this->getName(), $query, $this-▕
>prepareBindings($bindings), $e
    822                 );▕
    823             }▕
    824▕
   825             throw new QueryException(➜▕
    826                 $this->getName(), $query, $this-▕
>prepareBindings($bindings), $e
    827             );▕
    828         }▕
    829     }▕
```

```
  1   vendor\laravel\framework\src\Illuminate\Database\Connection.php:825
```

```
  2   vendor\laravel\framework\src\Illuminate\Database\Connection.php:571
      NunoMaduro\Collision\Exceptions\TestException::("SQLSTATE[HY000]: General
error: 1553 Cannot drop index 'beneficiaries_contract_id_customer_id_unique':
needed in
```

```
a foreign key constraint")
```

```
  Tests:    23 failed, 2 passed (2 assertions)
  Duration: 828.13s
```

```
C:\laragon\www\sgic-2.0(master -> origin)
```

```
λ
```

