import { useState } from 'react';
import { useFormik, Form, FormikProvider } from 'formik';
import * as Yup from 'yup';
import {
  Stack,
  TextField,
} from '@mui/material';
import { LoadingButton } from '@mui/lab';

export default function VerifyCodeForm() {
  const VerifyCodeSchema = Yup.object().shape({
    code1: Yup.string().required('Code is required'),
    code2: Yup.string().required('Code is required'),
    code3: Yup.string().required('Code is required'),
    code4: Yup.string().required('Code is required'),
    code5: Yup.string().required('Code is required'),
    code6: Yup.string().required('Code is required'),
  });

  const formik = useFormik({
    initialValues: {
      code1: '',
      code2: '',
      code3: '',
      code4: '',
      code5: '',
      code6: '',
    },
    validationSchema: VerifyCodeSchema,
    onSubmit: () => {
      console.log('Form submitted');
    },
  });

  const { errors, touched, isSubmitting, handleSubmit, getFieldProps } = formik;

  return (
    <FormikProvider value={formik}>
      <Form autoComplete="off" noValidate onSubmit={handleSubmit}>
        <Stack spacing={3}>
          <Stack direction="row" spacing={2} justifyContent="center">
            {[1, 2, 3, 4, 5, 6].map((num) => (
              <TextField
                key={num}
                {...getFieldProps(`code${num}`)}
                error={Boolean(touched[`code${num}`] && errors[`code${num}`])}
                helperText={touched[`code${num}`] && errors[`code${num}`]}
                placeholder="-"
                value={formik.values[`code${num}`]}
                onChange={(e) => {
                  const value = e.target.value;
                  if (value.length <= 1) {
                    formik.setFieldValue(`code${num}`, value);
                    if (value && num < 6) {
                      document.querySelector(`input[name="code${num + 1}"]`)?.focus();
                    }
                  }
                }}
                sx={{
                  width: { xs: 36, sm: 56 },
                  height: { xs: 36, sm: 56 },
                  '& .MuiInputBase-input': {
                    p: 0,
                    textAlign: 'center',
                    fontSize: { xs: '1.2rem', sm: '1.5rem' },
                  },
                }}
              />
            ))}
          </Stack>
        </Stack>

        <LoadingButton
          fullWidth
          size="large"
          type="submit"
          variant="contained"
          loading={isSubmitting}
          sx={{ mt: 3 }}
        >
          Verify
        </LoadingButton>
      </Form>
    </FormikProvider>
  );
} 