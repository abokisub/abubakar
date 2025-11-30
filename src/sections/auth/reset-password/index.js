import { useState } from 'react';
import { Link as RouterLink } from 'react-router-dom';
import { useFormik, Form, FormikProvider } from 'formik';
import * as Yup from 'yup';
import { Icon } from '@iconify/react';
import eyeFill from '@iconify/icons-eva/eye-fill';
import eyeOffFill from '@iconify/icons-eva/eye-off-fill';
import {
  Link,
  Stack,
  TextField,
  IconButton,
  InputAdornment,
} from '@mui/material';
import { LoadingButton } from '@mui/lab';

export default function ResetPasswordForm() {
  const [showPassword, setShowPassword] = useState(false);

  const ResetPasswordSchema = Yup.object().shape({
    email: Yup.string().email('Email must be a valid email address').required('Email is required'),
  });

  const formik = useFormik({
    initialValues: {
      email: '',
    },
    validationSchema: ResetPasswordSchema,
    onSubmit: () => {
      console.log('Form submitted');
    },
  });

  const { errors, touched, isSubmitting, handleSubmit, getFieldProps } = formik;

  const handleShowPassword = () => {
    setShowPassword((show) => !show);
  };

  return (
    <FormikProvider value={formik}>
      <Form autoComplete="off" noValidate onSubmit={handleSubmit}>
        <Stack spacing={3}>
          <TextField
            fullWidth
            autoComplete="username"
            type="email"
            label="Email address"
            {...getFieldProps('email')}
            error={Boolean(touched.email && errors.email)}
            helperText={touched.email && errors.email}
          />
        </Stack>

        <LoadingButton
          fullWidth
          size="large"
          type="submit"
          variant="contained"
          loading={isSubmitting}
          sx={{ mt: 3 }}
        >
          Send Reset Link
        </LoadingButton>

        <Stack direction="row" alignItems="center" justifyContent="center" sx={{ my: 2 }}>
          <Link component={RouterLink} variant="subtitle2" to="/login">
            Back to login
          </Link>
        </Stack>
      </Form>
    </FormikProvider>
  );
} 